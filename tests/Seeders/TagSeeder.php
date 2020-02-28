<?php

namespace App\Tests\Seeders;

use Plasticode\Models\Tag;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

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
