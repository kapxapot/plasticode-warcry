<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\RecipeCollection;
use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class RecipeRepositoryMock implements RecipeRepositoryInterface
{
    private RecipeCollection $recipes;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->recipes = RecipeCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?Recipe
    {
        return $this
            ->recipes
            ->first('id', $id);
    }

    public function getAllByItemId(int $itemId) : RecipeCollection
    {
        return $this
            ->recipes
            ->where(
                fn (Recipe $r) =>
                $r->createsId == $itemId && $r->createsMin > 0
            );
    }

    public function getByItemId(int $itemId) : ?Recipe
    {
        return $this
            ->getAllByItemId($itemId)
            ->first();
    }
}
