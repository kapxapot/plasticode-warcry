<?php

namespace App\Repositories;

use App\Collections\RecipeCollection;
use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class RecipeRepository extends IdiormRepository implements RecipeRepositoryInterface
{
    protected $entityClass = Recipe::class;

    public function get(?int $id) : ?Recipe
    {
        return $this->getEntity($id);
    }

    public function getAllByItemId(int $itemId) : RecipeCollection
    {
        return $this
            ->query()
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
