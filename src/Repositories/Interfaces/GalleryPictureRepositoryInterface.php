<?php

namespace App\Repositories\Interfaces;

use App\Models\GalleryPicture;
use Plasticode\Collection;

interface GalleryPictureRepositoryInterface
{
    public function get(int $id) : ?GalleryPicture;
    public function getByTag(string $tag, int $limit = null) : Collection;
}
