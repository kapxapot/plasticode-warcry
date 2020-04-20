<?php

namespace App\Repositories\Interfaces;

use App\Models\GalleryAuthor;

interface GalleryAuthorRepositoryInterface
{
    function get(?int $id) : ?GalleryAuthor;
}
