<?php

namespace App\Services;

use App\Models\GalleryAuthorCategory;
use App\Repositories\Interfaces\GalleryAuthorCategoryRepositoryInterface;
use Plasticode\Collections\Basic\ArrayCollection;

class GalleryAuthorService
{
    private GalleryAuthorCategoryRepositoryInterface $galleryAuthorCategoryRepository;

    public function __construct(
        GalleryAuthorCategoryRepositoryInterface $galleryAuthorCategoryRepository
    )
    {
        $this->galleryAuthorCategoryRepository = $galleryAuthorCategoryRepository;
    }

    public function getGroups() : ArrayCollection
    {
        return ArrayCollection::from(
            $this
                ->galleryAuthorCategoryRepository
                ->getAll()
                ->where(
                    fn (GalleryAuthorCategory $c) => $c->authors()->any()
                )
                ->map(
                    fn (GalleryAuthorCategory $c) =>
                    [
                        'id' => $c->alias,
                        'label' => $c->name,
                        'values' => $c->authors()->sortByName()
                    ]
                )
        );
    }
}
