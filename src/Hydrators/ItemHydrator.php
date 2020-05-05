<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Item;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ItemHydrator extends Hydrator
{
    private RecipeRepositoryInterface $recipeRepository;

    private LinkerInterface $linker;

    public function __construct(
        RecipeRepositoryInterface $recipeRepository,
        LinkerInterface $linker
    )
    {
        $this->recipeRepository = $recipeRepository;
        $this->linker = $linker;
    }

    /**
     * @param Item $entity
     */
    public function hydrate(DbModel $entity) : Item
    {
        return $entity
            ->withRecipes(
                fn () => $this->recipeRepository->getAllByItemId($entity->getId())
            )
            ->withUrl(
                fn () => $this->linker->wowheadItemRu($entity->getId())
            );
    }
}
