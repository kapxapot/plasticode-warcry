<?php

namespace App\Collections;

use App\Models\Video;
use Plasticode\TypedCollection;

class VideoCollection extends TypedCollection
{
    protected string $class = Video::class;
}
