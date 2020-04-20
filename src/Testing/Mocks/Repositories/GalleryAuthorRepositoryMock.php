<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\GalleryAuthorCollection;
use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GalleryAuthorRepositoryMock implements GalleryAuthorRepositoryInterface
{
    private GalleryAuthorCollection $authors;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->authors = GalleryAuthorCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?GalleryAuthor
    {
        return $this
            ->authors
            ->first('id', $id);
    }
}
