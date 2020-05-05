<?php

namespace App\Repositories\Interfaces;

use App\Collections\GalleryAuthorCategoryCollection;
use App\Models\GalleryAuthorCategory;

interface GalleryAuthorCategoryRepositoryInterface
{
    function get(?int $id) : ?GalleryAuthorCategory;
    function getAll() : GalleryAuthorCategoryCollection;
}
