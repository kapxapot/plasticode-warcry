<?php

namespace App\Collections;

use App\Models\GalleryPicture;
use Plasticode\Collections\Basic\DbModelCollection;

class GalleryPictureCollection extends DbModelCollection
{
    protected string $class = GalleryPicture::class;
}
