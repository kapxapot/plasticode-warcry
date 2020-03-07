<?php

namespace App\Repositories;

use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class GalleryAuthorRepository extends IdiormRepository implements GalleryAuthorRepositoryInterface
{
    protected $entityClass = GalleryAuthor::class;

    public function get(int $id) : ?GalleryAuthor
    {
        return $this->getEntity($id);
    }
}
