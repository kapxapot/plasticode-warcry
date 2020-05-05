<?php

namespace App\Repositories\Interfaces;

use App\Collections\GalleryAuthorCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryAuthorCategory;

interface GalleryAuthorRepositoryInterface
{
    function get(?int $id) : ?GalleryAuthor;

    function getAllPublishedByCategory(
        GalleryAuthorCategory $category
    ) : GalleryAuthorCollection;

    function getPublishedByAlias(string $alias) : ?GalleryAuthor;

    function getPrev(GalleryAuthor $author) : ?GalleryAuthor;
    function getNext(GalleryAuthor $author) : ?GalleryAuthor;
}
