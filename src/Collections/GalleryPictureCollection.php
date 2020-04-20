<?php

namespace App\Collections;

use App\Models\GalleryPicture;
use Plasticode\TypedCollection;

class GalleryPictureCollection extends TypedCollection
{
    protected string $class = GalleryPicture::class;
}
