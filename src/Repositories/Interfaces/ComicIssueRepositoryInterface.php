<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicIssueCollection;
use App\Models\ComicSeries;

interface ComicIssueRepositoryInterface
{
    /**
     * Returns all published comic issues by series.
     */
    function getAllBySeries(ComicSeries $series) : ComicIssueCollection;
}
