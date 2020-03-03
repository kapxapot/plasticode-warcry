<?php

namespace App\Tests\Seeders;

use App\Models\GalleryAuthor;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

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
