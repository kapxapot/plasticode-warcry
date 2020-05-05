<?php

namespace App\Collections;

use App\Models\GalleryAuthorCategory;
use Plasticode\Collections\Basic\DbModelCollection;

class GalleryAuthorCategoryCollection extends DbModelCollection
{
    protected string $class = GalleryAuthorCategory::class;
}
