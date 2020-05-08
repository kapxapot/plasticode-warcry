<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicPageCollection;
use App\Models\ComicIssue;

interface ComicPageRepositoryInterface
{
    function getAllByComic(ComicIssue $comic) : ComicPageCollection;
}
