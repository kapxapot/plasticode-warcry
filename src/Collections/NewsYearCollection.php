<?php

namespace App\Collections;

use App\Models\NewsYear;
use Plasticode\Collections\Basic\TypedCollection;

class NewsYearCollection extends TypedCollection
{
    protected string $class = NewsYear::class;

    /**
     * Sorts by year descending.
     *
     * @return static
     */
    public function sort() : self
    {
        return $this->desc(
            fn (NewsYear $y) => $y->year()
        );
    }
}
