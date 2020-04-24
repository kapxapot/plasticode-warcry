<?php

namespace App\Collections;

use App\Models\News;
use Plasticode\TypedCollection;

class NewsCollection extends TypedCollection
{
    protected string $class = News::class;
}
