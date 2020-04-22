<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class GalleryPictureHydrator extends Hydrator
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;

    public function __construct(
        GalleryAuthorRepositoryInterface $galleryAuthorRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker
    )
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;
    }

    /**
     * @param GalleryPicture $entity
     */
    public function hydrate(DbModel $entity) : GalleryPicture
    {
        return $entity
            ->withAuthor(
                fn () => $this->galleryAuthorRepository->get($entity->authorId)
            )
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            )
            ->withPrev(
                fn () => $this->galleryPictureRepository->getPrevSibling($entity)
            )
            ->withNext(
                fn () => $this->galleryPictureRepository->getNextSibling($entity)
            )
            ->withExt(
                fn () => $this->linker->getImageExtension($entity->pictureType)
            )
            ->withUrl(
                fn () => $this->linker->galleryPictureImg($entity)
            )
            ->withThumbUrl(
                fn () => $this->linker->galleryThumbImg($entity)
            )
            ->withPageUrl(
                fn () => $this->linker->galleryPicture($entity)
            );
    }
}
