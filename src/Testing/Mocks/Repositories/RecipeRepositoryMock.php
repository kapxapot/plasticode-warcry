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

    public function getFilteredCount(
        ?int $skillId,
        ?string $searchQuery
    ) : int
    {
        return $this
            ->getFiltered($skillId, $searchQuery)
            ->count();
    }

    public function getFilteredPage(
        ?int $skillId,
        ?string $searchQuery,
        int $offset,
        int $pageSize
    ) : RecipeCollection
    {
        return $this
            ->getFiltered($skillId, $searchQuery)
            ->slice($offset, $pageSize);
    }

    /**
     * Placeholder.
     */
    protected function getFiltered(
        ?int $skillId,
        ?string $searchQuery
    ) : RecipeCollection
    {
        return $this
            ->recipes
            ->where(
                fn (Recipe $r) => $r->skillId == $skillId
            );
    }

    public function getByName(string $name) : ?Recipe
    {
        return $this
            ->recipes
            ->first(
                fn (Recipe $r) => $r->name == $name || $r->nameRu == $name
            );
    }
}
