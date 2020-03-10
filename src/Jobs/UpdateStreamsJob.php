<?php

namespace App\Jobs;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Stream;
use App\Models\StreamStat;
use Plasticode\Collection;
use Plasticode\Contained;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\External\Twitch;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Psr\Log\LoggerInterface;

class UpdateStreamsJob extends Contained
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var CacheInterface */
    private $cache;

    /** @var LinkerInterface */
    private $linker;

    /** @var Twitch */
    private $twitch;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Send notification or not (Telegram, Twitter, etc.)
     *
     * @var boolean
     */
    private $notify;

    /**
     * Log or not
     *
     * @var boolean
     */
    private $log;
    
    public function __construct(
        SettingsProviderInterface $settingsProvider,
        CacheInterface $cache,
        LinkerInterface $linker,
        Twitch $twitch,
        LoggerInterface $logger,
        bool $notify
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->cache = $cache;
        $this->linker = $linker;
        $this->twitch = $twitch;
        $this->logger = $logger;
        
        $this->notify = $notify;

        $this->log = ($this->settingsProvider->getSettings('streams.log')) === true;
    }
    
    public function run() : Collection
    {
        return Stream::getPublished()
            ->all()
            ->map(
                function ($s) {
                    return $this->updateStream($s);
                }
            );
    }
    
    private function updateStream(Stream $stream) : array
    {
        $id = $stream->streamId;
        
        $data = $this->twitch->getStreamData($id);

        $s = $data['streams'][0] ?? null;

        if ($this->log) {
            if (is_null($s)) {
                $this->logger->debug('No stream data for id = ' . $id, $data);
            } else {
                $this->logger->info('Loaded stream data for id = ' . $id, $s);
            }
        }

        if ($s) {
            $streamStarted = !$stream->isOnline();
            
            $gameId = $s['game_id'];
            $game = $this->getGameData($gameId);

            $userId = $s['user_id'];
            $user = $this->getUserData($userId);

            $stream->remoteOnline = 1;
            $stream->remoteGame = $game['name'] ?? $gameId;
            $stream->remoteViewers = $s['viewer_count'];
            $stream->remoteTitle = $user['display_name'] ?? null;

            $sanitizedTitle = Strings::toUtf8($s['title']);
            $stream->remoteStatus = urlencode($sanitizedTitle);

            $stream->remoteLogo = $user['profile_image_url'] ?? null;
            
            $description = $user['description'] ?? null;
            
            if (!is_null($description)) {
                $stream->description = $description;
            }

            if ($this->notify && $streamStarted) {
                $message = $this->sendStreamNotifications($stream);
            }
        } else {
            $stream->remoteOnline = 0;
            $stream->remoteViewers = 0;
        }
        
        $now = Date::dbNow();
        
        $stream->remoteUpdatedAt = $now;

        if ($stream->remoteOnline == 1) {
            $stream->remoteOnlineAt = $now;
        }

        $stream->save();
        $this->updateStreamStats($stream);
        
        return [
            'stream' => $stream,
            'json' => json_encode($data),
            'message' => $message,
        ];
    }
    
    private function getGameData(string $id) : array
    {
        return $this->cache->getCached(
            'twitch_game_' . $id,
            function () use ($id) {
                $data = $this->twitch->getGameData($id);
                $game = $data['data'][0] ?? null;

                if ($this->log) {
                    if (is_null($game)) {
                        $this->logger->debug('No game data for id = ' . $id, $data);
                    } else {
                        $this->logger->info('Loaded game data for id = ' . $id, $game);
                    }
                }

                return $game;
            }
        );
    }
    
    private function getUserData(string $id) : array
    {
        return $this->cache->getCached(
            'twitch_user_' . $id,
            function () use ($id) {
                $data = $this->twitch->getUserData($id);
                $user = $data['data'][0] ?? null;

                if ($this->log) {
                    if (is_null($user)) {
                        $this->logger->debug('No user data for id = ' . $id, $data);
                    } else {
                        $this->logger->info('Loaded user data for id = ' . $id, $user);
                    }
                }

                return $user;
            }
        );
    }
    
    private function updateStreamStats(Stream $stream) : void
    {
        $online = $stream->isOnline();
        $refresh = $online;
        
        $stats = StreamStat::getLast($stream->id);
        
        if ($stats) {
            if ($online) {
                $statsTTL = $this->settingsProvider->getSettings('streams.stats_ttl', 10);

                $expired = Date::expired($stats->createdAt, "PT{$statsTTL}M");
    
                if (!$expired && ($stream->remoteGame == $stats->remoteGame)) {
                    $refresh = false;
                }
            }

            if (!$stats->finishedAt && (!$online || $refresh)) {
                $stats->finish();
            }
        }
        
        if ($refresh) {
            $stats = StreamStat::fromStream($stream);
            $stats->save();
        }
    }
    
    private function sendStreamNotifications(Stream $s) : string
    {
        $status = $s->remoteStatus;

        $verb = ($s->channel == 1)
            ? ($status
                ? "транслирует <b>{$status}</b>"
                : 'ведет трансляцию')
            : "играет в <b>{$s->remoteGame}</b>
{$status}";

        $verbEn = ($s->channel == 1)
            ? ($status
                ? "is streaming <b>{$status}</b>"
                : 'started streaming')
            : "is playing <b>{$s->remoteGame}</b>
{$status}";
        
        $url = $this->linker->twitch($s->streamId);
        $source = "<a href=\"{$url}\">{$s->title}</a>";
        
        $message = $source . ' ' . $verb;
        $messageEn = $source . ' ' . $verbEn;

        $settings = [
            [
                'channel' => 'warcry_streams',
                'condition' => true,
                'message' => $message,
            ],
            [
                'channel' => 'blizzard_streams',
                'condition' => $s->official == 1,
                'message' => $messageEn,
            ],
            [
                'channel' => 'blizzard_streams_ru',
                'condition' => $s->officialRu == 1,
                'message' => $message,
            ],
        ];

        foreach ($settings as $setting) {
            if ($setting['condition']) {
                $this->telegram->sendMessage(
                    $setting['channel'],
                    $setting['message']
                );
            }
        }

        return $message . ' ' . $messageEn;
    }
}
