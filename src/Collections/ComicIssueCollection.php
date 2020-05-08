<?php

namespace App\Collections;

use App\Models\ComicIssue;
use Plasticode\Collections\Basic\DbModelCollection;

class ComicIssueCollection extends DbModelCollection
{
    protected string $class = ComicIssue::class;

    public function byNumber(int $number) : ?ComicIssue
    {
        return $this->first(
            fn (ComicIssue $i) => $i->number == $number
        );
    }

    public function prev(int $number) : ?ComicIssue
    {
        return $this
            ->desc(
                fn (ComicIssue $i) => $i->number
            )
            ->first(
                fn (ComicIssue $i) => $i->number < $number
            );
    }

    public function next(int $number) : ?ComicIssue
    {
        return $this
            ->asc(
                fn (ComicIssue $i) => $i->number
            )
            ->first(
                fn (ComicIssue $i) => $i->number > $number
            );
    }
}
