<?php

namespace App\Collections;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\TypedCollection;
use Plasticode\Util\Sort;

class NewsSourceCollection extends TypedCollection
{
    protected string $class = NewsSourceInterface::class;

    /**
     * Sorts news in descending order by publish date.
     * Reverse sort = ascending order.
     * 
     * @return static
     */
    public function sort(bool $reverse = false) : self
    {
        return $this->orderBy(
            'published_at',
            $reverse ? Sort::DESC : Sort::ASC,
            Sort::DATE
        );
    }

    /**
     * Sorts news in ascending order by publish date.
     *
     * @return static
     */
    public function sortReverse() : self
    {
        return $this->sort(true);
    }
}
