<?php

namespace App\Repositories\Interfaces;

use App\Models\Recipe;
use Plasticode\Collection;

interface RecipeRepositoryInterface
{
    public function get(int $id) : ?Recipe;
    public function getAllByItemId(int $itemId) : Collection;
    public function getByItemId(int $itemId) : ?Recipe;
}
