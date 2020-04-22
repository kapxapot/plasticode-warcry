<?php

namespace App\Repositories\Interfaces;

use App\Collections\RecipeCollection;
use App\Models\Recipe;

interface RecipeRepositoryInterface
{
    function get(?int $id) : ?Recipe;
    function getAllByItemId(int $itemId) : RecipeCollection;
    function getByItemId(int $itemId) : ?Recipe;

    function getFilteredCount(
        ?int $skillId,
        ?string $searchQuery
    ) : int;

    function getFilteredPage(
        ?int $skillId,
        ?string $searchQuery,
        int $offset,
        int $pageSize
    ) : RecipeCollection;

    function getByName(string $name) : ?Recipe;
}
