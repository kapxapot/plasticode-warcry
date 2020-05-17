<?php

namespace App\Services;

use App\Collections\GalleryPictureCollection;
use App\Config\Interfaces\GalleryConfigInterface;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;

class GalleryService
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;

    private GalleryConfigInterface $config;

    public function __construct(
        GalleryAuthorRepositoryInterface $galleryAuthorRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        GalleryConfigInterface $config
    )
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;

        $this->config = $config;
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

    public function getChunk(
        ?GalleryPicture $borderPic = null,
        ?GalleryAuthor $author = null,
        ?string $tag = null
    ) : GalleryPictureCollection
    {
        return $this
            ->galleryPictureRepository
            ->getChunkBefore(
                $borderPic,
                $author,
                $tag,
                $this->config->galleryPicsPerPage()
            );
    }
}
