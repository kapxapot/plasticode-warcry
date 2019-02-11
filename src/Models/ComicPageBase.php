<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Publish;
use Plasticode\Models\Traits\Stamps;

abstract class ComicPageBase extends DbModel
{
    use Publish, Stamps;
    
    protected static $comicIdField;

    // GETTERS - MANY

    public static function getByComic($id)
    {
        return self::getAllPublished(function ($q) use ($id) {
			return $q
			    ->where(static::$comicIdField, $id)
			    ->orderByAsc('number');
		});
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

	protected function genericPrev()
	{
		return self::getPublishedWhere(function ($q) {
			return $q
				->where(static::$comicIdField, $this->{static::$comicIdField})
				->whereLt('number', $this->number)
				->orderByDesc('number');
		});
	}
	
	protected function genericNext()
	{
		return self::getPublishedWhere(function ($q) {
			return $q
				->where(static::$comicIdField, $this->{static::$comicIdField})
				->whereGt('number', $this->number)
				->orderByAsc('number');
		});
	}

	public function prev()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		$prev = $this->genericPrev();
    		
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
    		$next = $this->genericNext();

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
