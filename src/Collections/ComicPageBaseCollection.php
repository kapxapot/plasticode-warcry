<?php

namespace App\Collections;

use App\Models\ComicPageBase;
use Plasticode\Collections\Basic\DbModelCollection;

abstract class ComicPageBaseCollection extends DbModelCollection
{
    protected string $class = ComicPageBase::class;

    public function byNumber(int $number) : ?ComicPageBase
    {
        return $this->first(
            fn (ComicPageBase $i) => $i->number == $number
        );
    }

    public function maxNumber() : int
    {
        $max = $this
            ->asc(
                fn (ComicPageBase $p) => $p->number
            )
            ->last();

        return $max ? $max->number : 0;
    }
}
