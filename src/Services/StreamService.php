<?php

namespace App\Services;

use App\Config\Interfaces\StreamConfigInterface;
use Plasticode\Collection;
use Plasticode\Util\Cases;

use App\Models\Stream;
use Plasticode\Util\Date;

class StreamService
{
    /** @var StreamConfigInterface */
    private $config;

    /** @var Cases */
    private $cases;
    
    public function __construct(
        StreamConfigInterface $config,
        Cases $cases
    )
    {
        $this->config = $config;
        $this->cases = $cases;
    }
    
    public function getByTag($tag) : array
    {
        $streams = Stream::getByTag($tag)->all();

        return $this->arrange($this->sort($streams));
    }

    public function getAllSorted() : Collection
    {
        $streams = Stream::getPublished()->all();
        
        return $this->sort($streams);
    }

    public function getGroups()
    {
        $streams = self::getAllSorted();

        $groups = [
            [
                'id' => 'online',
                'label' => 'Онлайн',
                'telegram' => 'warcry_streams',
                'streams' => $streams->where(function ($s) {
                    return $s->remoteOnline;
                }),
            ],
            [
                'id' => 'offline',
                'label' => 'Офлайн',
                'telegram' => 'warcry_streams',
                'streams' => $streams->where(function ($s) {
                    return $s->alive() && !$s->remoteOnline;
                }),
            ],
            [
                'id' => 'blizzard',
                'label' => 'Blizzard EN',
                'telegram' => 'blizzard_streams',
                'telegram_label' => 'официальных трансляций (англ.)',
                'streams' => $streams->where(function ($s) {
                    return $s->official;
                }),
            ],
            [
                'id' => 'blizzard_ru',
                'label' => 'Blizzard РУ',
                'telegram' => 'blizzard_streams_ru',
                'telegram_label' => 'официальных трансляций (рус.)',
                'streams' => $streams->where(function ($s) {
                    return $s->officialRu;
                }),
            ],
        ];
        
        return array_map(function ($g) {
            $g['streams'] = $this->arrange($g['streams']);
            return $g;
        }, $groups);
    }
    
    private function arrange(Collection $streams) : array
    {
        return array_filter([
            $streams->where(function ($s) {
                return $s->isOnline();
            }),
            $streams->where(function ($s) {
                return !$s->isOnline() && $s->hasLogo();
            }),
            $streams->where(function ($s) {
                return !$s->isOnline() && !$s->hasLogo();
            }),
        ], function ($a) {
            return count($a) > 0;
        });
    }
    
    private function sort(Collection $streams) : Collection
    {
        $sorts = [
            'remote_online' => [ 'dir' => 'desc' ],
            'official_ru' => [ 'dir' => 'desc' ],
            'official' => [ 'dir' => 'desc' ],
            'priority' => [ 'dir' => 'desc' ],
            'priority_game' => [ 'dir' => 'desc' ],
            'remote_viewers' => [ 'dir' => 'desc' ],
            'remote_online_at' => [ 'dir' => 'desc', 'type' => 'string' ],
            'title' => [ 'dir' => 'asc', 'type' => 'string' ],
        ];
    
        return $streams->multiSort($sorts);
    }

    public function getAllOnline($game = null) : Collection
    {
        $online = $this->getAllSorted()
            ->where('remote_online', 1);

        if ($game) {
            $online = $online->where(function ($s) use ($game) {
                return $s->belongsToGame($game);
            });
        }

        return $online;
    }
    
    public function topOnline($game = null)
    {
        $stream = null;
        
        if ($game !== null && $game->default() === false) {
            $stream = $this->getAllOnline($game)->first();
        }
        
        return $stream ?? $this->getAllOnline()->first();
    }
    
    public function totalOnlineStr($game = null)
    {
        $totalOnline = $this->getAllOnline($game)->count();
        
        return $totalOnline . ' ' . $this->cases->caseForNumber('стрим', $totalOnline);
    }
    
    public function isAlive(Stream $stream) : bool
    {
        if (!$stream->remoteOnlineAt) {
            return false;
        }
        
        $timeToLive = $this->config->streamTimeToLive();
        $age = Date::age($stream->remoteOnlineAt);
        
        return $age->days < $timeToLive;
    }
}
