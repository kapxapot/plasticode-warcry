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
                $this->galleryAuthorRepository->get($entity->authorId)
            )
            ->withGame(
                $this->gameRepository->get($entity->gameId)
            )
            ->withPrev(
                $this->galleryPictureRepository->getPrevSibling($entity)
            )
            ->withNext(
                $this->galleryPictureRepository->getNextSibling($entity)
            )
            ->withExt(
                $this->linker->getImageExtension($entity->pictureType)
            )
            ->withUrl(
                $this->linker->galleryPictureImg($entity)
            )
            ->withThumbUrl(
                $this->linker->galleryThumbImg($entity)
            )
            ->withPageUrl(
                $this->linker->galleryPicture($entity)
            );
    }
}
