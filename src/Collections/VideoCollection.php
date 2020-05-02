<?php

namespace App\Collections;

use App\Models\Video;

class VideoCollection extends NewsSourceCollection
{
    protected string $class = Video::class;
}
