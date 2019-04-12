<?php

namespace App\Models;

class ComicStandalonePage extends ComicPageBase
{
    protected static $comicIdField = 'comic_standalone_id';

    // PROPS
    
    public function comic() : ComicStandalone
    {
        return ComicStandalone::get($this->{static::$comicIdField});
    }
    
    public function pageUrl()
    {
        return self::$linker->comicStandalonePage($this);
    }
}
