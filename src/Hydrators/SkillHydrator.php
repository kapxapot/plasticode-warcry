<?php

namespace App\Hydrators;

use App\Config\Interfaces\RecipeConfigInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Models\Skill;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class SkillHydrator extends Hydrator
{
    private LinkerInterface $linker;
    private RecipeConfigInterface $config;

    public function __construct(
        LinkerInterface $linker,
        RecipeConfigInterface $config
    )
    {
        $this->linker = $linker;
        $this->config = $config;
    }

    /**
     * @param Skill $entity
     */
    public function hydrate(DbModel $entity) : Skill
    {
        return $entity
            ->withDefaultIcon(
                fn () => $this->config->defaultWoWIcon()
            )
            ->withIconUrl(
                fn () => $this->linker->wowheadIcon($entity->displayIcon())
            );
    }
}
