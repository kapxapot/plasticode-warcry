<?php

namespace App\Models;

class ComicStandalone extends Comic
{
    protected static $tagsEntityType = 'comics';
    
    protected static $sortOrder = [
        [ 'field' => 'issued_on', 'reverse' => true ],
    ];
    
    // GETTERS - ONE

	public static function getPublishedByAlias($alias)
	{
		return self::getPublishedWhere(function($q) use ($alias) {
    		return $q->where('alias', $alias);
		});
	}

	// PROPS
	
    public function game()
    {
        return Game::get($this->gameId);
    }

    public function pageUrl()
    {
	    return self::$linker->comicStandalone($this);
	}
    
    public function pages()
    {
        return $this->lazy(__FUNCTION__, function () {
            return ComicStandalonePage::getByComic($this->id);
        });
    }

    public function publisher()
    {
        return ComicPublisher::get($this->publisherId);
    }

    public function titleName()
    {
        return $this->fullName();
    }
}
