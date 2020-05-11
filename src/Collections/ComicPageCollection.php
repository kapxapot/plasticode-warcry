<?php

namespace App\Collections;

use App\Collections\Interfaces\NumberedCollectionInterface;
use App\Collections\Traits\NumberedCollection;
use App\Models\ComicPage;
use Plasticode\Collections\Basic\DbModelCollection;

abstract class ComicPageCollection extends DbModelCollection implements NumberedCollectionInterface
{
    use NumberedCollection
    {
        byNumber as parentByNumber;
        prevBy as parentPrevBy;
        nextBy as parentNextBy;
    }

    protected string $class = ComicPage::class;

    public function byNumber(int $number) : ?ComicPage
    {
        return $this->parentByNumber($number);
    }

    public function prevBy(int $number) : ?ComicPage
    {
        return $this->parentPrevBy($number);
    }

    public function nextBy(int $number) : ?ComicPage
    {
        return $this->parentNextBy($number);
    }
}
