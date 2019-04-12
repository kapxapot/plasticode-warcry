<?php

namespace App\Models;

use Plasticode\Collection;

class ComicStandalone extends Comic
{
    protected static $tagsEntityType = 'comics';
    
    protected static $sortOrder = [
        [ 'field' => 'issued_on', 'reverse' => true ],
    ];
    
    // getters - one

	public static function getPublishedByAlias($alias)
	{
		return self::getPublished()
    		->where('alias', $alias)
    		->one();
	}
    
    // funcs
    
    public function createPage()
    {
        return ComicStandalonePage::createForComic($this->getId());
    }

	// props
	
    public function game()
    {
        return Game::get($this->gameId);
    }

    public function pageUrl()
    {
	    return self::$linker->comicStandalone($this);
	}
    
    public function pages() : Collection
    {
        return $this->lazy(__FUNCTION__, function () {
            return ComicStandalonePage::getByComic($this->id)
                ->all();
        });
    }

    public function publisher() : ComicPublisher
    {
        return ComicPublisher::get($this->publisherId);
    }

    public function titleName()
    {
        return $this->fullName();
    }
}
