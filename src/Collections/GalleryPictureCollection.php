<?php

namespace App\Collections;

use App\Models\GalleryPicture;
use Plasticode\Collections\Basic\TaggedCollection;

class GalleryPictureCollection extends TaggedCollection
{
    protected string $class = GalleryPicture::class;
}
