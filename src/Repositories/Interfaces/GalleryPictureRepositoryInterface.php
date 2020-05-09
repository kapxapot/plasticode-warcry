<?php

namespace App\Repositories\Interfaces;

use App\Collections\GalleryPictureCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use Plasticode\Repositories\Interfaces\Basic\TaggedRepositoryInterface;

interface GalleryPictureRepositoryInterface extends TaggedRepositoryInterface
{
    function get(?int $id) : ?GalleryPicture;

    function getAllByTag(
        string $tag,
        int $limit = 0
    ) : GalleryPictureCollection;

    /**
     * Returns all published pictures that were published before given.
     */
    function getAllBefore(GalleryPicture $pic) : GalleryPictureCollection;

    /**
     * Returns all published pictures that were published after given.
     */
    function getAllAfter(GalleryPicture $pic) : GalleryPictureCollection;

    /**
     * Returns all published pictures by author.
     */
    function getAllByAuthor(
        GalleryAuthor $author,
        int $limit = 0
    ) : GalleryPictureCollection;

    /**
     * Returns all published pictures by game.
     */
    function getAllByGame(
        ?Game $game,
        int $limit = 0
    ) : GalleryPictureCollection;

    /**
     * Returns the previous picture of the same author.
     */
    function getPrevSibling(GalleryPicture $pic) : ?GalleryPicture;

    /**
     * Returns the next picture of the same author.
     */
    function getNextSibling(GalleryPicture $pic) : ?GalleryPicture;

    function getAddedPicturesSlice(
        ?Game $game,
        \DateTime $start,
        \DateTime $end
    ) : GalleryPictureCollection;
}
