<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\GalleryPictureCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;

class GalleryPictureRepositoryMock implements GalleryPictureRepositoryInterface
{
    private GalleryPictureCollection $pictures;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->pictures = GalleryPictureCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?GalleryPicture
    {
        return $this
            ->pictures
            ->first('id', $id);
    }

    public function getAllByTag(
        string $tag,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        $pictures = $this
            ->pictures
            ->where(
                function (GalleryPicture $picture) use ($tag) {
                    $tags = Strings::toTags($picture->tags);
                    $normTag = Strings::normalize($tag);

                    return in_array($normTag, $tags);
                }
            );

        return GalleryPictureCollection::from(
            $limit
                ? $pictures->take($limit)
                : $pictures
        );
    }

    private function getAllPublished() : GalleryPictureCollection
    {
        return $this
            ->pictures
            ->where(
                fn (GalleryPicture $p) => $p->isPublished()
            );
    }

    /**
     * Returns all published pictures that were published before given.
     */
    public function getAllBefore(GalleryPicture $pic) : GalleryPictureCollection
    {
        return $this
            ->getAllPublished()
            ->where(
                fn (GalleryPicture $p) =>
                Date::dt($p->publishedAt) < Date::dt($pic->publishedAt)
                || Date::dt($p->publishedAt) == Date::dt($pic->publishedAt)
                && $p->getId() < $pic->getId()
            )
            ->sortBy(
                SortStep::byFuncDesc(
                    fn (GalleryPicture $p) => $p->publishedAt,
                    Sort::DATE
                ),
                SortStep::byFuncDesc(
                    fn (GalleryPicture $p) => $p->id 
                )
            );
    }

    /**
     * Returns all published pictures that were published after given.
     */
    public function getAllAfter(GalleryPicture $pic) : GalleryPictureCollection
    {
        return $this
            ->getAllPublished()
            ->where(
                fn (GalleryPicture $p) =>
                Date::dt($p->publishedAt) > Date::dt($pic->publishedAt)
                || Date::dt($p->publishedAt) == Date::dt($pic->publishedAt)
                && $p->getId() > $pic->getId()
            )
            ->sortBy(
                SortStep::byFunc(
                    fn (GalleryPicture $p) => $p->publishedAt,
                    Sort::DATE
                ),
                SortStep::byFunc(
                    fn (GalleryPicture $p) => $p->id 
                )
            );
    }

    /**
     * Returns all published pictures by author.
     */
    public function getAllByAuthor(
        GalleryAuthor $author,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        return $this
            ->getAllPublished()
            ->where(
                fn (GalleryPicture $p) => $p->authorId == $author->getId()
            );
    }

    /**
     * Returns all published pictures by game.
     */
    public function getAllByGame(
        ?Game $game = null,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        $result = $this->getAllPublished();

        return $game
            ? $result->where(
                fn (GalleryPicture $p) => $p->gameId == $game->getId()
            )
            : $result;
    }

    /**
     * Returns the previous picture of the same author.
     */
    function getPrevSibling(GalleryPicture $pic) : ?GalleryPicture
    {
        return $this->getAllBefore($pic)->first();
    }

    /**
     * Returns the next picture of the same author.
     */
    function getNextSibling(GalleryPicture $pic) : ?GalleryPicture
    {
        return $this->getAllAfter($pic)->first();
    }
}
