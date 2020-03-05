<?php

namespace App\Testing\Seeders;

use App\Models\Recipe;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class RecipeSeeder implements ArraySeederInterface
{
    /** @var Recipe[] */
    public function seed() : array
    {
        return [
            new Recipe(
                [
                    'id' => 1,
                    'name' => 'Gold Bar',
                    'name_ru' => 'Золотой слиток',
                    'creates_id' => 1,
                    'creates_min' => 1,
                ]
            )
        ];
    }
}
