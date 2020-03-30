<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\Traits\Description;
use Plasticode\Query;
use Plasticode\Util\Strings;

class Video extends NewsSource
{
    use Description;

    protected static string $sortField = 'published_at';
    protected static bool $sortReverse = true;

    // PROPS

    public function video() : ?string
    {
        return self::$container->linker->youtube($this->youtubeCode);
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
    
    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        return Strings::doubleBracketsTag('video', $this->getId(), $this->name);
    }
    
    // NewsSourceInterface

    public function url() : ?string
    {
        return self::$container->linker->video($this->getId());
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

    public static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query
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
}
