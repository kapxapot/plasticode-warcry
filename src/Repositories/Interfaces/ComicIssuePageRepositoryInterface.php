<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicIssuePageCollection;
use App\Models\Comic;
use App\Models\ComicIssuePage;

interface ComicIssuePageRepositoryInterface extends ComicPageRepositoryInterface
{
    function save(ComicIssuePage $page) : ComicIssuePage;
    function getAllByComic(Comic $comic) : ComicIssuePageCollection;
}
