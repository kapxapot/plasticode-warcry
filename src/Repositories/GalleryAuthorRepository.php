<?php

namespace App\Repositories;

use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Repositories\Idiorm\IdiormRepository;

class GalleryAuthorRepository extends IdiormRepository implements GalleryAuthorRepositoryInterface
{
    public function get(int $id) : ?GalleryAuthor
    {
        return GalleryAuthor::get($id);
    }
}
