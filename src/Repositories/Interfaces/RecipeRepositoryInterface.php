<?php

namespace App\Repositories\Interfaces;

use App\Collections\RecipeCollection;
use App\Models\Recipe;

interface RecipeRepositoryInterface
{
    function get(?int $id) : ?Recipe;
    function getAllByItemId(int $itemId) : RecipeCollection;
    function getByItemId(int $itemId) : ?Recipe;
}
