<?php

namespace App\Testing\Factories;

use App\Core\Interfaces\LinkerInterface;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use App\Testing\Mocks\Repositories\RecipeRepositoryMock;
use App\Testing\Mocks\Repositories\SkillRepositoryMock;
use App\Testing\Seeders\RecipeSeeder;
use App\Testing\Seeders\SkillSeeder;

class RecipeRepositoryFactory
{
    public static function make(
        LinkerInterface $linker
    ) : RecipeRepositoryInterface
    {
        $skillRepository = new SkillRepositoryMock(
            new SkillSeeder($linker)
        );

        return new RecipeRepositoryMock(
            new RecipeSeeder(
                $skillRepository,
                $linker
            )
        );
    }
}
