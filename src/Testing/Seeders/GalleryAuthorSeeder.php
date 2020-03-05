<?php

namespace App\Testing\Seeders;

use App\Models\GalleryAuthor;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GalleryAuthorSeeder implements ArraySeederInterface
{
    /**
     * @return GalleryAuthor[]
     */
    public function seed() : array
    {
        return [
            new GalleryAuthor(
                [
                    'id' => 1,
                    'alias' => 'author',
                    'categoryId' => 1,
                    'name' => 'Author',
                ]
            ),
        ];
    }
}
