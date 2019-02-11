<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;

class News extends DbModel
{
    use CachedDescription, FullPublish, Stamps, Tags;
    
    // traits
    
    protected static function getDescriptionField()
    {
        return 'text';
    }
    
    // getters - many
    
	public static function getLatest($game = null, $offset = 0, $limit = 0, $exceptId = null, $year = null)
	{
		return self::getAllPublished(function($query) use ($game, $offset, $limit, $exceptId, $year) {
			if ($exceptId) {
				$query = $query->whereNotEqual(static::$idField, $exceptId);
			}
			
			if ($game) {
    			$query = $game->filter($query);
			}
			
			$query = $query->orderByDesc('published_at');
			
			if ($offset > 0 || $limit > 0) {
				$query = $query
					->offset($offset)
					->limit($limit);
			}
			
			if ($year > 0) {
				$query = $query->whereRaw('(year(published_at) = ?)', [ $year ]);
			}
			
			return $query;
		});
	}
	
	public static function getByYear($year)
	{
		return self::getLatest(null, 0, 0, null, $year);
	}
	
	public static function getByGame($game, $exceptId = null)
	{
	    return self::getLatest($game, 0, 0, $exceptId);
	}

    // props
    
    public function game()
    {
        return Game::get($this->gameId);
    }
    
    public function url()
    {
        return self::$linker->news($this->getId());
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
