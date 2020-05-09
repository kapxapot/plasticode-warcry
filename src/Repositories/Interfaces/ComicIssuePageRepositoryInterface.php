<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicIssuePageCollection;
use App\Models\Comic;

interface ComicIssuePageRepositoryInterface extends ComicPageRepositoryInterface
{
    function getAllByComic(Comic $comic) : ComicIssuePageCollection;
}
