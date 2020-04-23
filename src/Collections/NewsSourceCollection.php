<?php

namespace App\Collections;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\TypedCollection;

abstract class NewsSourceCollection extends TypedCollection
{
    protected string $class = NewsSourceInterface::class;
}
