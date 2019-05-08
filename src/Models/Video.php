<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Moment;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;

use App\Models\Interfaces\NewsSourceInterface;

class Video extends DbModel implements SearchableInterface, NewsSourceInterface
{
    use Description, FullPublish, Stamps, Tags;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;

    // PROPS
    
    public function game()
    {
        return $this->gameId
            ? Game::get($this->gameId)
            : null;
    }

    public function toString()
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
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        $parts = [
            "video:{$this->getId()}",
            $this->name,
        ];
        
        $code = self::$parser->joinTagParts($parts);
        
        return "[[{$code}]]";
    }
    
    // NewsSourceInterface

    public function url()
    {
        return self::$linker->video($this->getId());
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
        return $this->description;
    }
    
    public function shortText()
    {
        return $this->description;
    }
}
