<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicStandalonePageCollection;
use App\Models\Comic;
use App\Models\ComicStandalonePage;

interface ComicStandalonePageRepositoryInterface extends ComicPageRepositoryInterface
{
    function save(ComicStandalonePage $page) : ComicStandalonePage;
    function getAllByComic(Comic $comic) : ComicStandalonePageCollection;
}
