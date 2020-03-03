<?php

namespace App\Repositories\Interfaces;

use App\Models\GalleryAuthor;

interface GalleryAuthorRepositoryInterface
{
    public function get(int $id) : ?GalleryAuthor;
}
