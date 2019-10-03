<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
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

class Event extends DbModel implements SearchableInterface, NewsSourceInterface
{
    use CachedDescription, FullPublish, Stamps, Tags;
    
    // queries
    
    public static function getOrderedByStart() : Query
    {
        return self::getPublished()
            ->orderByAsc('starts_at')
            ->orderByAsc('ends_at');
    }

    private static function filterUnended(Query $query) : Query
    {
        return $query
            ->whereRaw('(coalesce(ends_at, date_add(date(starts_at), interval 24*60*60 - 1 second)) >= now() or unknown_end = 1)');
    }
    
    private static function filterCurrent(Query $query, int $days) : Query
    {
        return $query
            ->whereRaw("(starts_at < date_add(now(), interval {$days} day) or important = 1)");
    }
    
    private static function filterFuture(Query $query) : Query
    {
        return $query->whereRaw('(starts_at > now())');
    }
    
    public static function getUnended() : Query
    {
        $query = self::getOrderedByStart();
        return self::filterUnended($query);
    }
    
    public static function getCurrent(?Game $game, int $days) : Query
    {
        $query = self::getUnended();
        $query = self::filterCurrent($query, $days);

        if ($game) {
            return $game->filter($query);
        }
        
        return $query->where('announce', 1);
    }
    
    public static function getFutureImportant() : Query
    {
        $query = self::getOrderedByStart();
        $query = self::filterFuture($query);
        
        return $query->where('important', 1);
    }
    
    // getters - many
    
    public static function getGroups() : array
    {
        $events = self::getOrderedByStart()->all();
        
        $groups = [
            [
                'id' => 'current',
                'label' => 'Текущие',
                'items' => $events->where(
                    function ($e) {
                        return $e->started() && !$e->ended();
                    }
                ),
            ],
            [
                'id' => 'future',
                'label' => 'Будущие',
                'items' => $events->where(
                    function ($e) {
                        return !$e->started();
                    }
                ),
            ],
            [
                'id' => 'past',
                'label' => 'Прошедшие',
                'items' => $events->where(
                    function ($e) {
                        return $e->ended();
                    }
                ),
            ]
        ];

        return $groups;
    }
    
    // PROPS
    
    public function game() : ?Game
    {
        return Game::get($this->gameId);
    }
    
    public function region() : ?Region
    {
        return Region::get($this->regionId);
    }
    
    public function type() : EventType
    {
        return EventType::get($this->typeId);
    }

    public function started() : bool
    {
        return Date::happened($this->startsAt);
    }

    public function ended() : bool
    {
        return ($this->unknownEnd != 1)
            && Date::happened($this->guessEndsAt());
    }
    
    public function start() : Moment
    {
        return new Moment($this->startsAt);
    }
    
    public function end() : ?Moment
    {
        return $this->endsAt
            ? new Moment($this->endsAt)
            : null;
    }

    /**
     * @return string|\DateTime
     */
    public function guessEndsAt()
    {
        return $this->endsAt ?? Date::endOfDay($this->startsAt);
    }

    public function parsed() : array
    {
        return $this->parsedDescription();
    }
    
    public function parsedText() : string
    {
        return $this->parsed()['text'];
    }

    public function toString() : string
    {
        return '[' . $this->id . '] ' . $this->name;
    }

    // interfaces

    public static function search(string $searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(name like ?)')
            ->orderByAsc('name')
            ->all();
    }
    
    public function serialize() : ?array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        $parts = [
            'event:' . $this->getId(),
            $this->name,
        ];
        
        $code = self::$parser->joinTagParts($parts);
        
        return '[[' . $code . ']]';
    }
    
    // NewsSourceInterface

    public function url() : ?string
    {
        return self::$linker->event($this->id);
    }
    
    private static function announced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
    
    public static function getNewsByTag(string $tag) : Query
    {
        $query = static::getByTag($tag);
        return self::announced($query);
    }
    
    private static function getNewsByGame(Game $game = null) : Query
    {
        $query = self::getPublished();

        if ($game) {
            $query = $game->filter($query);
        }

        return self::announced($query);
    }

    public static function getLatestNews(Game $game = null, int $exceptNewsId = null) : Query
    {
        return self::getNewsByGame($game)
            ->orderByDesc('published_at');
    }
    
    public static function getNewsBefore(Game $game, string $date) : Query
    {
        return self::getNewsByGame($game)
            ->whereLt('published_at', $date)
            ->orderByDesc('published_at');
    }
    
    public static function getNewsAfter(Game $game, string $date) : Query
    {
        return self::getNewsByGame($game)
            ->whereGt('published_at', $date)
            ->orderByAsc('published_at');
    }
    
    public static function getNewsByYear(int $year) : Query
    {
        $query = self::getPublished()
            ->whereRaw('(year(published_at) = ?)', [$year]);
        
        return self::announced($query);
    }
    
    public function displayTitle() : string
    {
        return $this->name;
    }
    
    public function fullText() : string
    {
        return $this->lazy(
            function () {
                return self::$parser->parseCut(
                    $this->parsedText()
                );
            }
        );
    }
    
    public function shortText() : string
    {
        return $this->lazy(
            function () {
                return self::$parser->parseCut(
                    $this->parsedText(), $this->url(), false
                );
            }
        );
    }
}
