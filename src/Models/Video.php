<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Strings;

class Video extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use Description, FullPublish, Stamps, Tags;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;

    // PROPS
    
    public function game() : ?Game
    {
        return $this->gameId
            ? Game::get($this->gameId)
            : null;
    }

    public function largeImage() : ?string
    {
        return null;
    }

    public function image() : ?string
    {
        return null;
    }

    public function video() : ?string
    {
        return self::$linker->youtube($this->youtubeCode);
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
            'video:' . $this->getId(),
            $this->name,
        ];
        
        $code = self::$parser->joinTagParts($parts);
        
        return '[[' . $code . ']]';
    }
    
    // NewsSourceInterface

    public function url() : ?string
    {
        return self::$linker->video($this->getId());
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
