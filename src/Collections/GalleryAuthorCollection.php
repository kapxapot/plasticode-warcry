<?php

namespace App\Collections;

use App\Models\GalleryAuthor;
use Plasticode\TypedCollection;

class GalleryAuthorCollection extends TypedCollection
{
    protected string $class = GalleryAuthor::class;
}
