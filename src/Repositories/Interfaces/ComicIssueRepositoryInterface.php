<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicIssueCollection;
use App\Models\ComicIssue;
use App\Models\ComicSeries;
use Plasticode\Repositories\Interfaces\Basic\TaggedRepositoryInterface;

interface ComicIssueRepositoryInterface extends TaggedRepositoryInterface
{
    function get(?int $id) : ?ComicIssue;

    /**
     * Returns all published comic issues by series.
     */
    function getAllBySeries(ComicSeries $series) : ComicIssueCollection;

    function getAllByTag(string $tag, int $limit = 0) : ComicIssueCollection;
}
