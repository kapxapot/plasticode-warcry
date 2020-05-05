<?php

namespace App\Hydrators;

use App\Models\GalleryAuthorCategory;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class GalleryAuthorCategoryHydrator extends Hydrator
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;

    public function __construct(
        GalleryAuthorRepositoryInterface $galleryAuthorRepository
    )
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
    }

    /**
     * @param GalleryAuthorCategory $entity
     */
    public function hydrate(DbModel $entity) : GalleryAuthorCategory
    {
        return $entity
            ->withAuthors(
                fn () =>
                $this
                    ->galleryAuthorRepository
                    ->getAllPublishedByCategory($entity)
            );
    }
}
