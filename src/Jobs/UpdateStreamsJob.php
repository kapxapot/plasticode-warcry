<?php

namespace App\Jobs;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Stream;
use App\Models\StreamStat;
use App\Repositories\Interfaces\StreamRepositoryInterface;
use App\Repositories\Interfaces\StreamStatRepositoryInterface;
use App\Services\StreamStatService;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Psr\Log\LoggerInterface;

class UpdateStreamsJob
{
    private SettingsProviderInterface $settingsProvider;
    private CacheInterface $cache;
    private LinkerInterface $linker;
    private Twitch $twitch;
    private Telegram $telegram;
    private LoggerInterface $logger;

    private StreamRepositoryInterface $streamRepository;
    private StreamStatRepositoryInterface $streamStatRepository;

    private StreamStatService $streamStatService;

    /**
     * Send notification or not (Telegram, Twitter, etc.)
     */
    private bool $notify;

    /**
     * Log or not
     */
    private bool $log;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        CacheInterface $cache,
        LinkerInterface $linker,
        Twitch $twitch,
        Telegram $telegram,
        LoggerInterface $logger,
        StreamRepositoryInterface $streamRepository,
        StreamStatRepositoryInterface $streamStatRepository,
        StreamStatService $streamStatService,
        bool $notify
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->cache = $cache;
        $this->linker = $linker;
        $this->twitch = $twitch;
        $this->telegram = $telegram;
        $this->logger = $logger;

        $this->streamRepository = $streamRepository;
        $this->streamStatRepository = $streamStatRepository;

        $this->streamStatService = $streamStatService;

        $this->notify = $notify;

        $logSettings = $this->settingsProvider->get('streams.log');
        $this->log = ($logSettings === true);
    }

    public function run() : Collection
    {
        return $this
            ->streamRepository
            ->getAllPublished()
            ->map(
                fn (Stream $s) => $this->updateStream($s)
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

        $stream = $this->streamRepository->save($stream);

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

        $stat = $this->streamStatRepository->getLastByStream($stream);

        if ($stat) {
            if ($online) {
                $statsTTL = $this
                    ->settingsProvider
                    ->get('streams.stats_ttl', 10);

                $expired = Date::expired($stat->createdAt, "PT{$statsTTL}M");

                if (!$expired && ($stream->remoteGame == $stat->remoteGame)) {
                    $refresh = false;
                }
            }

            if (!$stat->finishedAt && (!$online || $refresh)) {
                $stats = $this->streamStatService->finishStat($stat);
            }
        }

        if ($refresh) {
            $stat = StreamStat::fromStream($stream);
            $this->streamStatRepository->save($stat);
        }
    }

    private function sendStreamNotifications(Stream $stream) : string
    {
        $status = $stream->remoteStatus;

        $verb = $stream->isChannel()
            ? ($status
                ? 'транслирует <b>' . $status . '</b>'
                : 'ведет трансляцию')
            : 'играет в <b>' . $stream->remoteGame . '</b>' . PHP_EOL . $status;

        $verbEn = $stream->isChannel()
            ? ($status
                ? 'is streaming <b>' . $status . '</b>'
                : 'started streaming')
            : 'is playing <b>' . $stream->remoteGame . '</b>' . PHP_EOL . $status;

        $url = $this->linker->twitch($stream->streamId);

        $source = '<a href="' . $url . '">' . $stream->title . '</a>';

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
                'condition' => $stream->isOfficial(),
                'message' => $messageEn,
            ],
            [
                'channel' => 'blizzard_streams_ru',
                'condition' => $stream->isOfficialRu(),
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
