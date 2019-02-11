<?php

namespace App\Models;

class ComicIssue extends Comic
{
    protected static $sortField = 'number';

    // GETTERS - MANY

    public static function getBySeries($seriesId)
    {
        return self::getAllPublished(function ($q) use ($seriesId) {
			return $q->where('series_id', $seriesId);
		});
    }
    
    // PROPS
    
    public function series()
    {
        return ComicSeries::get($this->seriesId);
    }
    
    public function pages()
    {
        return $this->lazy(__FUNCTION__, function () {
            return ComicPage::getByComic($this->id);
        });
    }

	public function numberStr()
	{
		$numStr = '#' . $this->number;
		
		if ($this->nameRu) {
			$numStr .= ': ' . $this->nameRu;
		}

		return $numStr;
	}
    
    public function pageUrl()
    {
        return self::$linker->comicIssue($this);
    }

    public function prev()
	{
		return $this->lazy(__FUNCTION__, function () {
		    return self::getPublishedWhere(function ($q) {
    			return $q
    				->where('series_id', $this->seriesId)
    				->whereLt('number', $this->number)
    				->orderByDesc('number');
		    });
		});
	}
    
    public function next()
	{
		return $this->lazy(__FUNCTION__, function () {
		    return self::getPublishedWhere(function ($q) {
    			return $q
    				->where('series_id', $this->seriesId)
    				->whereGt('number', $this->number)
    				->orderByAsc('number');
		    });
		});
	}
	
	public function titleName()
	{
	    $name = $this->series()->name() . ' ' . $this->numberStr();
	    
	    if ($this->series()->subName()) {
	        $name .= ' (' . $this->series()->subName() . ')';
	    }
	    
	    return $name;
	}
}
