<?php

namespace App\Collections;

use App\Collections\Interfaces\NumberedCollectionInterface;
use App\Collections\Traits\NumberedCollection;
use App\Models\ComicIssue;
use Plasticode\Collections\Basic\TaggedCollection;

final class ComicIssueCollection extends TaggedCollection implements NumberedCollectionInterface
{
    use NumberedCollection
    {
        byNumber as parentByNumber;
        prevBy as parentPrevBy;
        nextBy as parentNextBy;
    }

    protected string $class = ComicIssue::class;

    public function byNumber(int $number) : ?ComicIssue
    {
        return $this->parentByNumber($number);
    }

    public function prevBy(int $number) : ?ComicIssue
    {
        return $this->parentPrevBy($number);
    }

    public function nextBy(int $number) : ?ComicIssue
    {
        return $this->parentNextBy($number);
    }
}
