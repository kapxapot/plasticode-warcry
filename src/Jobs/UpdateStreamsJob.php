<?php

namespace App\Jobs;

use App\Models\Stream;
use App\Models\StreamStat;
use Plasticode\Collection;
use Plasticode\Contained;
use Plasticode\Util\Date;
use Psr\Container\ContainerInterface;

class UpdateStreamsJob extends Contained
{
    /**
     * Send notification or not (Telegram, Twitter, etc.)
     *
     * @var boolean
     */
    private $notify;
    
    public function __construct(ContainerInterface $container, bool $notify)
    {
        parent::__construct($container);
        
        $this->notify = $notify;
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
            $stream->remoteStatus = urlencode($s['title']);
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
    
    private function getGameData(string $id)
    {
        return $this->cache->getCached(
            'twitch_game_' . $id,
            function () use ($id) {
                $data = $this->twitch->getGameData($id);
                return $data['data'][0] ?? null;
            }
        );
    }
    
    private function getUserData(string $id)
    {
        return $this->cache->getCached(
            'twitch_user_' . $id,
            function () use ($id) {
                $data = $this->twitch->getUserData($id);
                return $data['data'][0] ?? null;
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
                $statsTTL = $this->getSettings('streams.stats_ttl');

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
