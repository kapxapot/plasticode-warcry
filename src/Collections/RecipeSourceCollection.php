<?php

namespace App\Collections;

use App\Models\RecipeSource;
use Plasticode\TypedCollection;

class RecipeSourceCollection extends TypedCollection
{
    protected string $class = RecipeSource::class;
}
