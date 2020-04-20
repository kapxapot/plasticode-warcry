<?php

namespace App\Collections;

use App\Models\Recipe;
use Plasticode\TypedCollection;

class RecipeCollection extends TypedCollection
{
    protected string $class = Recipe::class;
}
