<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicPageCollection;
use App\Models\Comic;

interface ComicPageRepositoryInterface
{
    function getAllByComic(Comic $comic) : ComicPageCollection;
}
