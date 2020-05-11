<?php

namespace App\Repositories;

use App\Collections\GalleryAuthorCategoryCollection;
use App\Models\GalleryAuthorCategory;
use App\Repositories\Interfaces\GalleryAuthorCategoryRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class GalleryAuthorCategoryRepository extends IdiormRepository implements GalleryAuthorCategoryRepositoryInterface
{
    protected string $entityClass = GalleryAuthorCategory::class;

    protected string $sortField = 'position';

    public function get(?int $id) : ?GalleryAuthorCategory
    {
        return $this->getEntity($id);
    }

    public function getAll() : GalleryAuthorCategoryCollection
    {
        return GalleryAuthorCategoryCollection::from(
            $this->query()
        );
    }
}
