<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Query;
use Plasticode\Util\Strings;

class News extends NewsSource
{
    use CachedDescription;

    protected static $sortField = 'published_at';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getDescriptionField() : string
    {
        return 'text';
    }

    // interfaces

    public static function search(string $searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(title like ?)')
            ->orderByAsc('title')
            ->all();
    }
    
    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->displayTitle(),
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        return Strings::doubleBracketsTag('news', $this->getId(), $this->displayTitle());
    }
    
    // LinkableInterface
    
    public function url() : ?string
    {
        return self::$container->linker->news($this->getId());
    }
    
    // NewsSourceInterface
    
    public static function getNewsByTag(string $tag) : Query
    {
        return static::getByTag($tag);
    }
    
    private static function getNewsByGame(Game $game = null) : Query
    {
        $query = self::getPublished();

        if ($game) {
            $query = $game->filter($query);
        }

        return $query;
    }

    public static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query
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
}
