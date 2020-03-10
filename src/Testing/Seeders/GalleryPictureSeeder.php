<?php

namespace App\Testing\Seeders;

use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GalleryPictureSeeder implements ArraySeederInterface
{
    /** @var GalleryAuthorRepositoryInterface */
    private $galleryAuthorRepository;

    public function __construct(GalleryAuthorRepositoryInterface $galleryAuthorRepository)
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
    }

    /** @var GalleryPicture[] */
    public function seed() : array
    {
        $author = $this->galleryAuthorRepository->get(1);

        return [
            (new GalleryPicture(
                [
                    'id' => 1,
                    'comment' => 'Sexy elf',
                    'width' => 200,
                    'height' => 100,
                    'tags' => 'Elves, Sex',
                ]
            ))->withAuthor($author),
            (new GalleryPicture(
                [
                    'id' => 2,
                    'comment' => 'Dead man',
                    'width' => 100,
                    'height' => 300,
                    'tags' => 'Humans, Undead',
                ]
            ))->withAuthor($author),
        ];
    }
}
