<?php

namespace App\Services;

use App\Collections\GalleryPictureCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use Plasticode\Util\Date;

class GalleryService
{
    private int $pageSize;

    public function __construct(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getAddedPicturesSliceByAuthor(
        ?Game $game,
        \DateTime $start,
        \DateTime $end
    ) : array
    {
        $slices = $this
            ->getAddedPicturesSlice($game, $start, $end)
            ->group('author_id');

        $result = [];

        foreach ($slices as $authorId => $pictures) {
            $result[] = [
                'author' => GalleryAuthor::get($authorId),
                'pictures' => $pictures,
            ];
        }

        return $result;
    }

    public function getAddedPicturesSlice(
        ?Game $game,
        \DateTime $start,
        \DateTime $end
    ) : GalleryPictureCollection
    {
        return GalleryPicture::getByGame($game)
            ->whereGt('published_at', Date::formatDb($start))
            ->whereLt('published_at', Date::formatDb($end))
            ->all();
    }
}
