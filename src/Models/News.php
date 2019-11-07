<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Strings;

class News extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use CachedDescription, FullPublish, Stamps, Tags;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getDescriptionField() : string
    {
        return 'text';
    }

    // props
    
    public function game() : ?Game
    {
        return Game::get($this->gameId);
    }

    public function largeImage() : ?string
    {
        $parsed = $this->parsed();
        
        return $parsed['large_image'] ?? null;
    }
    
    public function image() : ?string
    {
        $parsed = $this->parsed();
        
        return $parsed['image'] ?? null;
    }

    public function video() : ?string
    {
        $parsed = $this->parsed();
        
        return $parsed['video'] ?? null;
    }
    
    public function parsed() : array
    {
        return $this->parsedDescription();
    }
    
    public function parsedText() : string
    {
        return $this->parsed()['text'];
    }

    // interfaces

    public static function search(string $searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(title like ?)')
            ->orderByAsc('title')
            ->all();
    }
    
    public function serialize() : ?array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->displayTitle(),
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        $parts = [
            'news:' . $this->getId(),
            $this->displayTitle(),
        ];
        
        $code = self::$parser->joinTagParts($parts);

        return '[[' . $code . ']]';
    }
    
    // LinkableInterface
    
    public function url() : ?string
    {
        return self::$linker->news($this->getId());
    }
    
    // NewsSourceInterface
    
    public static function getNewsByTag(string $tag) : Query
    {
        return static::getByTag($tag);
    }
    
    private static function getNewsByGame(Game $game = null) : Query
    {
        $query = self::getBasePublished();

        if ($game) {
            $query = $game->filter($query);
        }

        return $query;
    }

    public static function getLatestNews(Game $game = null, int $exceptNewsId = null) : Query
    {
        $query = self::getNewsByGame($game)
            ->orderByDesc('published_at');

        if ($exceptNewsId) {
            $query = $query->whereNotEqual(static::$idField, $exceptNewsId);
        }

        return $query;
    }
    
    public static function getNewsByYear(int $year) : Query
    {
        return self::getPublished()
            ->whereRaw('(year(published_at) = ?)', [$year]);
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
    
    public function displayTitle() : string
    {
        return $this->title;
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
