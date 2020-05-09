<?php

namespace App\Collections;

use App\Models\ComicIssuePage;

class ComicIssuePageCollection extends ComicPageCollection
{
    protected string $class = ComicIssuePage::class;

    public function byNumber(int $number) : ?ComicIssuePage
    {
        return parent::byNumber($number);
    }
}
