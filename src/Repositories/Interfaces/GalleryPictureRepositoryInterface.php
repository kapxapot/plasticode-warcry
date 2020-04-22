<?php

namespace App\Repositories\Interfaces;

use App\Collections\GalleryPictureCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;

interface GalleryPictureRepositoryInterface
{
    function get(?int $id) : ?GalleryPicture;
    function getByTag(string $tag, int $limit = 0) : GalleryPictureCollection;

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
        ?Game $game = null,
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
}
