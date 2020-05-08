<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicStandalonePageCollection;
use App\Models\ComicStandalone;

interface ComicStandalonePageRepositoryInterface
{
    function getAllByComic(
        ComicStandalone $comic
    ) : ComicStandalonePageCollection;
}
