<?php

namespace App\Models;

class ComicPage extends ComicPageBase
{
    protected static $comicIdField = 'comic_issue_id';
    
    // PROPS
    
    public function comic() : ComicIssue
    {
        return ComicIssue::get($this->{static::$comicIdField});
    }
    
    public function pageUrl() : string
    {
        return self::$container->linker->comicIssuePage($this);
    }
}
