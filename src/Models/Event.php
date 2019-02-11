<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Moment;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Date;

use App\Models\EventType;
use App\Models\Game;
use App\Models\Region;

class Event extends DbModel
{
    use CachedDescription, FullPublish, Stamps, Tags;
    
    protected static $sortOrder = [
        [ 'field' => 'starts_at' ],
		[ 'field' => 'ends_at' ],
    ];
    
	public static function getGroups()
	{
	    $events = self::getAllPublished();
	    
		$groups = [
			[
				'id' => 'current',
				'label' => 'Текущие',
				'items' => $events->where(function ($e) {
					return $e->started() && !$e->ended();
				}),
			],
			[
				'id' => 'future',
				'label' => 'Будущие',
				'items' => $events->where(function($e) {
					return !$e->started();
				}),
			],
			[
				'id' => 'past',
				'label' => 'Прошедшие',
				'items' => $events->where(function($e) {
					return $e->ended();
				}),
			]
		];

		return $groups;
	}
	
	// GETTERS - MANY
	
	public static function getCurrent($game, $days)
	{
		return self::getAllPublished(function($q) use ($game, $days) {
			$q = $q
				->whereRaw('(coalesce(ends_at, date_add(date(starts_at), interval 24*60*60 - 1 second)) >= now() or unknown_end = 1)')
				->whereRaw("(starts_at < date_add(now(), interval {$days} day) or important = 1)");

			if ($game) {
			    $q = $game->filter($q);
			}
			else {
				$q = $q->where('announce', 1);
			}
			
			return $q;
		});
	}

    // PROPS
    
    public function game()
    {
        return Game::get($this->gameId);
    }
    
    public function region()
    {
        return Region::get($this->regionId);
    }
    
    public function type()
    {
        return EventType::get($this->typeId);
    }

    public function url()
    {
        return self::$linker->event($this->id);
    }

    public function started()
    {
        return Date::happened($this->startsAt);
    }

    public function ended()
    {
		return ($this->unknownEnd != 1) && Date::happened($this->guessEndsAt());
    }
    
    public function start()
    {
        return new Moment($this->startsAt);
    }
    
    public function end()
    {
        return $this->endsAt
            ? new Moment($this->endsAt)
            : null;
    }

    public function guessEndsAt()
    {
        return $this->endsAt ?? Date::endOfDay($this->startsAt);
    }

    public function __toString()
    {
        return "[{$this->id}] {$this->name}";
    }
}
