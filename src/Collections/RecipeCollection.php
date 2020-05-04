<?php

namespace App\Collections;

use App\Models\Recipe;
use Plasticode\Collections\Basic\DbModelCollection;

class RecipeCollection extends DbModelCollection
{
    protected string $class = Recipe::class;
}
