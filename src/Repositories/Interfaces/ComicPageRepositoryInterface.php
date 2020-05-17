<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicPageCollection;
use App\Models\Comic;
use App\Models\ComicPage;

interface ComicPageRepositoryInterface
{
    function getAllByComic(Comic $comic) : ComicPageCollection;
}
