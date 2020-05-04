<?php

namespace App\Models;

use Plasticode\Collections\Basic\Collection;

class ComicStandalone extends Comic
{
    protected static string $sortField = 'issued_on';
    protected static bool $sortReverse = true;
    protected static $tagsEntityType = 'comics';

    public static function getPublishedByAlias($alias) : ?self
    {
        return self::getPublished()
            ->where('alias', $alias)
            ->one();
    }

    public function createPage() : ComicStandalonePage
    {
        return ComicStandalonePage::createForComic($this->getId());
    }

    public function game() : Game
    {
        return Game::get($this->gameId);
    }

    public function pageUrl() : string
    {
        return self::$container->linker->comicStandalone($this);
    }
    
    public function pages(bool $ignoreCache = false) : Collection
    {
        return $this->lazy(
            function () {
                return ComicStandalonePage::getByComic($this->id)
                    ->all();
            },
            null,
            $ignoreCache
        );
    }

    public function publisher() : ComicPublisher
    {
        return ComicPublisher::get($this->publisherId);
    }

    public function titleName() : string
    {
        return $this->fullName();
    }
}
