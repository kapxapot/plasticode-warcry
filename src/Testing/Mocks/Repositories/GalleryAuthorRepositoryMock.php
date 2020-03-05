<?php

namespace App\Testing\Mocks\Repositories;

use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GalleryAuthorRepositoryMock implements GalleryAuthorRepositoryInterface
{
    /** @var Collection */
    private $authors;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->authors = Collection::make($seeder->seed());
    }

    public function get(int $id) : ?GalleryAuthor
    {
        return $this
            ->authors
            ->where('id', $id)
            ->first();
    }
}
