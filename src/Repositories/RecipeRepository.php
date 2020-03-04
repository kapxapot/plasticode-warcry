<?php

namespace App\Repositories;

use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Repositories\Idiorm\IdiormRepository;

class RecipeRepository extends IdiormRepository implements RecipeRepositoryInterface
{
    public function get(int $id) : ?Recipe
    {
        return Recipe::get($id);
    }

    public function getAllByItemId(int $itemId) : Collection
    {
        return Recipe::query()
            ->where('creates_id', $itemId)
            ->whereGt('creates_min', 0)
            ->all();
    }
    
    public function getByItemId(int $itemId) : ?Recipe
    {
        return $this
            ->getAllByItemId($itemId)
            ->first();
    }
}
