<?php

namespace App\Testing\Seeders;

use Plasticode\Models\Tag;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TagSeeder implements ArraySeederInterface
{
    /**
     * @return Tag[]
     */
    public function seed() : array
    {
        return [
            new Tag(
                [
                    'id' => 1,
                    'tag' => 'warcraft',
                    'entity_type' => 'articles',
                    'entity_id' => 2
                ]
            ),
        ];
    }
}
