<?php

namespace App\Collections;

use App\Models\ComicIssue;

class ComicIssueCollection extends NumberedCollection
{
    protected string $class = ComicIssue::class;

    public function byNumber(int $number) : ?ComicIssue
    {
        return parent::byNumber($number);
    }

    public function prev(int $number) : ?ComicIssue
    {
        return parent::prev($number);
    }

    public function next(int $number) : ?ComicIssue
    {
        return parent::next($number);
    }
}
