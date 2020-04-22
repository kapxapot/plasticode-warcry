<?php

namespace App\Testing\Seeders;

use App\Models\Game;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GameSeeder implements ArraySeederInterface
{
    /**
     * @return Game[]
     */
    public function seed() : array
    {
        return [
            new Game(
                [
                    'id' => 1,
                    'name' => 'Warcraft',
                    'alias' => 'warcraft',
                    'published' => 1,
                ]
            )
        ];
    }
}
