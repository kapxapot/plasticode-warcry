<?php

namespace App\Testing\Mocks\Repositories;

use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class RecipeRepositoryMock implements RecipeRepositoryInterface
{
    /** @var Collection */
    private $recipes;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->recipes = Collection::make($seeder->seed());
    }

    public function get(int $id) : ?Recipe
    {
        return $this
            ->recipes
            ->where('id', $id)
            ->first();
    }

    public function getAllByItemId(int $itemId) : Collection
    {
        return $this
            ->recipes
            ->where('creates_id', $itemId)
            ->where(
                function (Recipe $recipe) {
                    return $recipe->createsMin > 0;
                }
            );
    }
    
    public function getByItemId(int $itemId) : ?Recipe
    {
        return $this
            ->getAllByItemId($itemId)
            ->first();
    }
}
