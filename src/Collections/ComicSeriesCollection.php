<?php

namespace App\Collections;

use App\Models\ComicSeries;
use Plasticode\Collections\Basic\TaggedCollection;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

class ComicSeriesCollection extends TaggedCollection
{
    protected string $class = ComicSeries::class;

    /**
     * Sorts descending by last issued on date.
     */
    public function sort() : self
    {
        return $this
            ->sortBy(
                SortStep::byFuncDesc(
                    fn (ComicSeries $s) => $s->lastIssuedOn(),
                    Sort::STRING
                )
            );
    }
}
