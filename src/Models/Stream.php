<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Cases;
use Plasticode\Util\Date;

class Stream extends DbModel
{
    use Description, FullPublish, Stamps;

    use Tags
    {
        Tags::getByTag as protected parentGetByTag;
    }
    
    protected static $sortField = 'remote_viewers';
    protected static $sortReverse = true;
    
    // traits
    
	public static function getByTag($tag, $where = null)
	{
	    $streams = self::parentGetByTag($tag, $where);

	    return self::arrange(self::sort($streams));
	}

    // misc
    
	public static function getGroups()
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
		    $g['streams'] = self::arrange($g['streams']);
		    return $g;
		}, $groups);
	}
	
	private static function arrange(Collection $streams)
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
	
	// GETTERS - MANY
	
	private static function sort(Collection $streams)
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
	
	public static function getAllSorted()
	{
	    return self::staticLazy(__FUNCTION__, function() {
	        return self::sort(self::getAllPublished());
	    });
	}
	
	public static function getAllOnline($game = null)
	{
	    $online = self::getAllSorted()->where('remote_online', 1);
	    
	    if ($game) {
	        $online = $online->where(function ($s) use ($game) {
                return $s->belongsToGame($game);
            });
	    }
	    
	    return $online;
	}

	// GETTERS - ONE
	
	public static function getPublishedByAlias($alias)
	{
		return self::getPublishedWhere(function($q) use ($alias) {
    		return $q->whereRaw('(stream_alias = ? or (stream_alias is null and stream_id = ?))', [ $alias, $alias ]);
		});
	}

	// PROPS - STATIC
	
	public static function topOnline($game = null)
	{
	    return self::getAllOnline($game)->first() ?? self::getAllOnline()->first();
	}
	
	public static function totalOnlineStr($game = null)
	{
		$totalOnline = self::getAllOnline($game)->count();
		return $totalOnline . ' ' . self::$cases->caseForNumber('стрим', $totalOnline);
	}

    // PROPS
    
    public function alive()
    {
        if (!$this->remoteOnlineAt) {
            return false;
        }
        
		$timeToLive = self::getSettings('streams.ttl');
		$age = Date::age($this->remoteOnlineAt);
			
		return $age->days < $timeToLive;
    }
    
    public function game()
    {
        return $this->lazy(__FUNCTION__, function () {
            return $this->remoteGame
                ? Game::getByTwitchName($this->remoteGame)
                : null;
        });
    }
    
    public function belongsToGame($game)
    {
        if (is_null($game) || is_null($this->game())) {
            return false;
        }
        
        return $game->root()->subTreeContains($this->game());
    }
    
    public function priorityGame()
    {
        return $this->official || $this->officialRu || Game::isPriority($this->remoteGame);
    }
    
    public function alias()
    {
        return $this->streamAlias ?? $this->streamId;
    }
    
    public function pageUrl()
    {
        return self::$linker->stream($this->alias());
    }
    
    public function imgUrl()
    {
        return self::$linker->twitchImg($this->streamId);
    }
    
    public function largeImgUrl()
    {
        return self::$linker->twitchLargeImg($this->streamId);
    }
    
    public function twitch()
    {
        return true;
    }
    
    public function streamUrl()
    {
        return self::$linker->twitch($this->streamId);
    }
    
    public function verbs()
    {
		$form = [
			'time' => Cases::PAST,
			'person' => Cases::FIRST,
			'number' => Cases::SINGLE,
			'gender' => $this->genderId,
		];
		
		return [
		    'played' => self::$cases->conjugation('играть', $form),
		    'broadcasted' => self::$cases->conjugation('транслировать', $form),
		    'held' => self::$cases->conjugation('вести', $form),
		];
    }
    
    public function nouns()
    {
        return [
            'viewers' => self::$cases->caseForNumber('зритель', $this->remoteViewers),
        ];
    }
    
    public function remoteOnlineAtIso()
    {
        return $this->remoteOnlineAt
            ? Date::iso($this->remoteOnlineAt)
            : null;
    }
    
    public function isOnline()
    {
        return $this->remoteOnline == 1;
    }
    
    public function hasLogo()
    {
        return strlen($this->remoteLogo) > 0;
    }

	public function displayRemoteStatus()
	{
	    return urldecode($this->remoteStatus);
	}
}
