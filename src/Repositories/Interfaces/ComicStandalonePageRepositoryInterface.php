<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicStandalonePageCollection;
use App\Models\Comic;

interface ComicStandalonePageRepositoryInterface extends ComicPageRepositoryInterface
{
    function getAllByComic(Comic $comic) : ComicStandalonePageCollection;
}
