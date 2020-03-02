<?php

namespace App\Tests\Seeders;

use App\Models\GalleryPicture;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

class GalleryPictureSeeder implements ArraySeederInterface
{
    /** @var GalleryPicture[] */
    public function seed() : array
    {
        return [
            new GalleryPicture(
                [
                    'id' => 1,
                    'comment' => 'Sexy elf',
                    'tags' => 'Elves, Sex',
                ]
            ),
            new GalleryPicture(
                [
                    'id' => 2,
                    'comment' => 'Dead man',
                    'tags' => 'Humans, Undead',
                ]
            ),
        ];
    }
}
