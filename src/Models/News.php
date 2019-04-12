<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Strings;

use App\Models\Interfaces\NewsSourceInterface;

class News extends DbModel implements SearchableInterface, NewsSourceInterface
{
    use CachedDescription, FullPublish, Stamps, Tags;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getDescriptionField()
    {
        return 'text';
    }

    // props
    
    public function game()
    {
        return Game::get($this->gameId);
    }

    public function largeImage()
    {
        $parsed = $this->parsed();
        
        return $parsed['large_image'];
    }
    
    public function image()
    {
        $parsed = $this->parsed();
        
        return $parsed['image'];
    }
    
    public function parsed()
    {
        return $this->parsedDescription();
    }
    
    public function parsedText()
    {
        return $this->parsed()['text'];
    }

    // interfaces

    public static function search($searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(title like ?)')
            ->orderByAsc('title')
            ->all();
    }
    
    public function serialize()
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
            "news:{$this->getId()}",
            $this->displayTitle(),
        ];
        
        $code = self::$parser->joinTagParts($parts);

        return "[[{$code}]]";
    }
    
    // LinkableInterface
    
    public function url()
    {
        return self::$linker->news($this->getId());
    }
    
    // NewsSourceInterface
    
    public static function getNewsByTag($tag) : Query
    {
        return static::getByTag($tag);
    }
    
    private static function getNewsByGame($game = null) : Query
    {
		$query = self::getBasePublished();

		if ($game) {
			$query = $game->filter($query);
		}

		return $query;
    }

	public static function getLatestNews($game = null, $exceptNewsId = null) : Query
	{
		$query = self::getNewsByGame($game)
		    ->orderByDesc('published_at');

		if ($exceptId) {
			$query = $query->whereNotEqual(static::$idField, $exceptId);
		}

		return $query;
	}
	
	public static function getNewsByYear($year) : Query
	{
		return self::getPublished()
		    ->whereRaw('(year(published_at) = ?)', [ $year ]);
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
    
    public function displayTitle()
    {
        return $this->title;
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
