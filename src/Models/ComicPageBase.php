<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Publish;
use Plasticode\Models\Traits\Stamps;

abstract class ComicPageBase extends DbModel
{
    use Publish, Stamps;

    protected static $sortField = 'number';

    protected static $comicIdField;

    // queries

    public static function getByComic($comicId) : Query
    {
        return self::getPublished()
		    ->where(static::$comicIdField, $comicId);
    }
    
    // funcs
    
    public static function createForComic($comicId)
    {
        return self::create([
            $comicIdField => $comicId
        ]);
    }
    
    // PROPS
    
    public abstract function comic();

    public abstract function pageUrl();

    public function url()
    {
        return self::$linker->comicPageImg($this);
    }

    public function thumbUrl()
    {
        return self::$linker->comicThumbImg($this);
    }

	public function numberStr()
	{
		return str_pad($this->number, 2, '0', STR_PAD_LEFT);
	}
	
	public function ext()
	{
	    return self::$linker->getExtension($this->type);
	}
	
    private function getSiblings() : Query
    {
        return self::getBasePublished()
		    ->where(static::$comicIdField, $this->{static::$comicIdField});
    }

	public function prev()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		$prev = $this->getSiblings()
				->whereLt('number', $this->number)
				->orderByDesc('number')
				->one();

    		if (!$prev) {
    			$prevComic = $this->comic()->prev();
    			
    			if ($prevComic) {
    			    $prev = $prevComic->last();
    			}
    		}
    		
    		return $prev;
	    });
	}
	
	public function next()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		$next = $this->getSiblings()
				->whereGt('number', $this->number)
				->orderByAsc('number')
				->one();

    		if (!$next) {
    			$nextComic = $this->comic()->next();
    			
    			if ($nextComic) {
    				$next = $nextComic->first();
    			}
    		}
    		
    		return $next;
	    });
	}
	
	public function titleName()
	{
	    return $this->numberStr() . ' - ' . $this->comic()->titleName();
	}
}
