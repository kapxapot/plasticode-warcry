<?php

namespace App\Testing\Seeders;

use App\Core\Interfaces\LinkerInterface;
use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GalleryPictureSeeder implements ArraySeederInterface
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;

    public function __construct(
        GalleryAuthorRepositoryInterface $galleryAuthorRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker
    )
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;
    }

    /**
     * @return GalleryPicture[]
     */
    public function seed() : array
    {
        $pic1 = new GalleryPicture(
            [
                'id' => 1,
                'author_id' => 1,
                'comment' => 'Sexy elf',
                'width' => 200,
                'height' => 100,
                'tags' => 'Elves, Sex',
                'pictureType' => 'jpeg',
            ]
        );

        $pic2 = new GalleryPicture(
            [
                'id' => 2,
                'author_id' => 1,
                'comment' => 'Dead man',
                'width' => 100,
                'height' => 300,
                'tags' => 'Humans, Undead',
                'pictureType' => 'png',
            ]
        );

        return [
            $this->hydrate($pic1),
            $this->hydrate($pic2),
        ];
    }

    private function hydrate(GalleryPicture $pic) : GalleryPicture
    {
        $author = $this->galleryAuthorRepository->get(1);
        $game = $this->gameRepository->getDefault();

        return $pic
            ->withAuthor(
                $author
            )
            ->withGame($game)
            ->withExt(
                fn () => $this->linker->getImageExtension($pic->pictureType)
            )
            ->withUrl(
                fn () => $this->linker->galleryPictureImg($pic)
            )
            ->withThumbUrl(
                fn () => $this->linker->galleryThumbImg($pic)
            )
            ->withPageUrl(
                fn () => $this->linker->galleryPicture($pic)
            );
    }
}
