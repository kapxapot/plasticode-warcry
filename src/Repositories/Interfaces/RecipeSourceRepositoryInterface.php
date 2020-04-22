<?php

namespace App\Repositories\Interfaces;

use App\Models\RecipeSource;

interface RecipeSourceRepositoryInterface
{
    function get(?int $id) : ?RecipeSource;
}
