<?php

namespace App\Models;

class ComicPage extends ComicPageBase
{
    protected static string $comicIdField = 'comic_issue_id';

    public function comic() : ComicIssue
    {
        return ComicIssue::get($this->{static::$comicIdField});
    }

    public function pageUrl() : string
    {
        return self::$container->linker->comicIssuePage($this);
    }
}
