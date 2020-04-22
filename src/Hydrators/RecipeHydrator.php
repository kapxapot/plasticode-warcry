<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeSourceRepositoryInterface;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class RecipeHydrator extends Hydrator
{
    private RecipeSourceRepositoryInterface $recipeSourceRepository;
    private SkillRepositoryInterface $skillRepository;

    private LinkerInterface $linker;

    public function __construct(
        RecipeSourceRepositoryInterface $recipeSourceRepository,
        SkillRepositoryInterface $skillRepository,
        LinkerInterface $linker
    )
    {
        $this->recipeSourceRepository = $recipeSourceRepository;
        $this->skillRepository = $skillRepository;

        $this->linker = $linker;
    }

    /**
     * @param Recipe $entity
     */
    public function hydrate(DbModel $entity) : Recipe
    {
        return $entity
            ->withSkill(
                fn () => $this->skillRepository->get($entity->skillId)
            )
            ->withSources(
                fn () => $this->sourceToArray($entity)
            )
            ->withUrl(
                fn () => $this->linker->recipe($entity->getId())
            );
    }

    /**
     * @return string[]
     */
    private function sourceToArray(Recipe $recipe) : array
    {
        $srcIds = explode(',', $recipe->source);

        return array_map(
            function ($srcId) {
                $src = $this->recipeSourceRepository->get($srcId);
                return $src ? $src->nameRu : $srcId;
            },
            $srcIds
        );
    }
}
