<?php

namespace App\Testing\Seeders;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Recipe;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class RecipeSeeder implements ArraySeederInterface
{
    private SkillRepositoryInterface $skillRepository;
    private LinkerInterface $linker;

    public function __construct(
        SkillRepositoryInterface $skillRepository,
        LinkerInterface $linker
    )
    {
        $this->skillRepository = $skillRepository;
        $this->linker = $linker;
    }

    /**
     * @return Recipe[]
     */
    public function seed() : array
    {
        $recipe = new Recipe(
            [
                'id' => 1,
                'name' => 'Gold Bar',
                'name_ru' => 'Золотой слиток',
                'creates_id' => 1,
                'creates_min' => 1,
                'skill_id' => 1,
            ]
        );

        return [
            $recipe
                ->withUrl(
                    $this->linker->recipe($recipe->getId())
                )
                ->withSources(['a', 'b', 'c'])
                ->withSkill(
                    $this->skillRepository->get($recipe->skillId)
                )
        ];
    }
}
