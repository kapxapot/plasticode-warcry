<?php

namespace App\Collections;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Collections\Basic\ScalarCollection;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;

class NewsSourceCollection extends DbModelCollection
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

    public function years() : ScalarCollection
    {
        return
            ScalarCollection::from(
                $this->map(
                    fn (NewsSourceInterface $item) =>
                    Date::year(
                        $item->publishedAtIso()
                    )
                )
            )
            ->distinct();
    }
}
