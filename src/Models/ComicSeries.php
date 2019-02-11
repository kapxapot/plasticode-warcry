<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Sort;

use App\Models\Traits\Names;

class ComicSeries extends DbModel
{
    use Description, FullPublish, Stamps, Tags, Names;
    
    protected static $tagsEntityType = 'comics';
    
    // GETTERS - ONE

	public static function getPublishedByAlias($alias)
	{
		return self::getPublishedWhere(function($q) use ($alias) {
    		return $q->where('alias', $alias);
		});
	}

    // GETTERS - MANY
    
	public static function getAllSorted()
	{
	    $sorts = [
            'last_issued_on' => [ 'dir' => 'desc', 'type' => 'string' ],
		];
	    
		return self::getAll()->multiSort($sorts);
	}
	
	// PROPS
	
    public function game()
    {
        return Game::get($this->gameId);
    }

    public function pageUrl()
    {
	    return self::$linker->comicSeries($this);
	}
	
    public function issues()
    {
        return $this->lazy(__FUNCTION__, function () {
            return ComicIssue::getBySeries($this->id);
        });
    }
    
    public function issueByNumber($number)
    {
        return $this->issues()->where('number', $number)->first();
    }
    
    public function count()
    {
        return $this->issues()->count();
    }
    
    public function countStr()
    {
		return self::$cases->caseForNumber('выпуск', $this->count());
    }
    
    public function first()
    {
        return $this->issues()->first();
    }
    
    public function last()
    {
        return $this->issues()->last();
    }
    
    public function cover()
    {
        return $this->first()
            ? $this->first()->cover()
            : null;
    }
    
    public function lastIssuedOn()
    {
        return $this->last()
            ? $this->last()->issuedOn
            : null;
    }
    
    public function publisher()
    {
        return ComicPublisher::get($this->publisherId);
    }
    
	public function maxIssueNumber($exceptId = null)
	{
	    $max = $this->issues()
	        ->where(function ($issue) use ($exceptId) {
	            return $issue->id != $exceptId;
	        })
	        ->asc('number')
	        ->last();
	    
	    return $max ? $max->number : 0;
	}
}
