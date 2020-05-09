<?php

namespace App\Models;

/**
 * @property integer $comicStandaloneId
 * @method static withComic(ComicStandalone|callable $comic)
 */
class ComicStandalonePage extends ComicPage
{
    protected static string $comicIdField = 'comic_standalone_id';

    public function comicId() : int
    {
        return $this->comicStandaloneId;
    }

    public function comic() : ComicStandalone
    {
        return parent::comic();
    }
}
