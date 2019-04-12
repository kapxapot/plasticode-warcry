<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Moment;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;

use App\Models\Interfaces\NewsSourceInterface;

class Event extends DbModel implements SearchableInterface, NewsSourceInterface
{
    use CachedDescription, FullPublish, Stamps, Tags;
    
    // queries
    
    public static function getOrderedByStart()
    {
        return self::getPublished()
            ->orderByAsc('starts_at')
            ->orderByAsc('ends_at');
    }
	
	public static function getCurrent($game, $days) : Query
	{
		$query = self::getOrderedByStart()
		    ->whereRaw('(coalesce(ends_at, date_add(date(starts_at), interval 24*60*60 - 1 second)) >= now() or unknown_end = 1)')
			->whereRaw("(starts_at < date_add(now(), interval {$days} day) or important = 1)");

		if ($game) {
		    return $game->filter($query);
		}
		
		return $query->where('announce', 1);
	}
    
    // getters - many
    
	public static function getGroups()
	{
	    $events = self::getOrderedByStart()->all();
	    
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

    public function parsed()
    {
        return $this->parsedDescription();
    }
    
    public function parsedText()
    {
        return $this->parsed()['text'];
    }

    public function __toString()
    {
        return "[{$this->id}] {$this->name}";
    }

    // interfaces

    public static function search($searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(name like ?)')
            ->orderByAsc('name')
            ->all();
    }
    
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'tags' => $this->tags,
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        $parts = [
            "event:{$this->getId()}",
            $this->name,
        ];
        
        $code = self::$parser->joinTagParts($parts);
        
        return "[[{$code}]]";
    }
    
    // NewsSourceInterface

    public function url()
    {
        return self::$linker->event($this->id);
    }
    
    private static function announced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
    
    public static function getNewsByTag($tag) : Query
    {
        $query = static::getByTag($tag);
        return self::announced($query);
    }
    
    private static function getNewsByGame($game = null) : Query
    {
		$query = self::getPublished();

		if ($game) {
			$query = $game->filter($query);
		}

		return self::announced($query);
    }

	public static function getLatestNews($game = null, $exceptNewsId = null) : Query
	{
	    return self::getNewsByGame($game)
	        ->orderByDesc('published_at');
	}
	
	public static function getNewsBefore($game, $date) : Query
	{
		return self::getNewsByGame($game)
		    ->whereLt('published_at', $date)
		    ->orderByDesc('published_at');
	}
	
	public static function getNewsAfter($game, $date) : Query
	{
		return self::getNewsByGame($game)
		    ->whereGt('published_at', $date)
		    ->orderByAsc('published_at');
	}
	
	public static function getNewsByYear($year) : Query
	{
		$query = self::getPublished()
		    ->whereRaw('(year(published_at) = ?)', [ $year ]);
		
		return self::announced($query);
	}
    
    public function displayTitle()
    {
        return $this->name;
    }
    
    public function fullText()
    {
        return $this->lazy(__FUNCTION__, function () {
            return self::$parser->parseCut($this->parsedText());
        });
    }
    
    public function shortText()
    {
        return $this->lazy(__FUNCTION__, function () {
            return self::$parser->parseCut($this->parsedText(), $this->url(), false);
        });
    }
}
