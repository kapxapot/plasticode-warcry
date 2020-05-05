<?php

namespace App\Services;

use App\Models\Game;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;

class GalleryService
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;

    private int $pageSize;

    public function __construct(
        GalleryAuthorRepositoryInterface $galleryAuthorRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        int $pageSize
    )
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;

        $this->pageSize = $pageSize;
    }

    public function getAddedPicturesSliceByAuthor(
        ?Game $game,
        \DateTime $start,
        \DateTime $end
    ) : array
    {
        $slices = $this
            ->galleryPictureRepository
            ->getAddedPicturesSlice($game, $start, $end)
            ->group('author_id');

        $result = [];

        foreach ($slices as $authorId => $pictures) {
            $result[] = [
                'author' => $this->galleryAuthorRepository->get($authorId),
                'pictures' => $pictures,
            ];
        }

        return $result;
    }
}
