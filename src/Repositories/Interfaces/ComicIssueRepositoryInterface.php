<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicIssueCollection;
use App\Models\ComicIssue;
use App\Models\ComicSeries;

interface ComicIssueRepositoryInterface
{
    function get(?int $id) : ?ComicIssue;

    /**
     * Returns all published comic issues by series.
     */
    function getAllBySeries(ComicSeries $series) : ComicIssueCollection;
}
