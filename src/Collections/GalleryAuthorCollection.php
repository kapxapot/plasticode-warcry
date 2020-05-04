<?php

namespace App\Collections;

use App\Models\GalleryAuthor;
use Plasticode\Collections\Basic\DbModelCollection;

class GalleryAuthorCollection extends DbModelCollection
{
    protected string $class = GalleryAuthor::class;
}
