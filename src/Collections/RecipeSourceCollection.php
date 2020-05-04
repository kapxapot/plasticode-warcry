<?php

namespace App\Collections;

use App\Models\RecipeSource;
use Plasticode\Collections\Basic\DbModelCollection;

class RecipeSourceCollection extends DbModelCollection
{
    protected string $class = RecipeSource::class;
}
