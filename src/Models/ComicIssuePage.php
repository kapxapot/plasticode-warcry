<?php

namespace App\Models;

/**
 * @property integer $comicIssueId
 * @method static withComic(ComicIssue|callable $comic)
 */
class ComicIssuePage extends ComicPage
{
    protected static string $comicIdField = 'comic_issue_id';

    public function comicId() : int
    {
        return $this->comicIssueId;
    }

    public function comic() : ComicIssue
    {
        return parent::comic();
    }
}
