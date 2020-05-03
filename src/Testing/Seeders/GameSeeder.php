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
                    'parent_id' => null,
                ]
            ),
            new Game(
                [
                    'id' => 2,
                    'name' => 'Warcraft III',
                    'alias' => null,
                    'published' => 0,
                    'parent_id' => 1,
                ]
            ),
            new Game(
                [
                    'id' => 3,
                    'name' => 'World of Warcraft',
                    'alias' => null,
                    'published' => 0,
                    'parent_id' => 1,
                ]
            ),
            new Game(
                [
                    'id' => 4,
                    'name' => 'The Burning Crusade',
                    'alias' => null,
                    'published' => 0,
                    'parent_id' => 3,
                ]
            ),
            new Game(
                [
                    'id' => 5,
                    'name' => 'Diablo',
                    'alias' => 'diablo',
                    'published' => 1,
                    'parent_id' => null,
                ]
            ),
            new Game(
                [
                    'id' => 6,
                    'name' => 'Diablo III',
                    'alias' => null,
                    'published' => 0,
                    'parent_id' => 5,
                ]
            ),
        ];
    }
}
