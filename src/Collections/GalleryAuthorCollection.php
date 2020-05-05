<?php

namespace App\Collections;

use App\Models\GalleryAuthor;
use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

class GalleryAuthorCollection extends DbModelCollection
{
    protected string $class = GalleryAuthor::class;

    public function sortByName() : self
    {
        return $this
            ->sortBy(
                SortStep::byFunc(
                    fn (GalleryAuthor $a) => $a->displayName(),
                    Sort::STRING
                )
            );
    }
}
