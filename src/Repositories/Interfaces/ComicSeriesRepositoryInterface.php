<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicSeriesCollection;
use App\Models\ComicSeries;
use Plasticode\Repositories\Interfaces\Basic\TaggedRepositoryInterface;

interface ComicSeriesRepositoryInterface extends TaggedRepositoryInterface
{
    function get(?int $id) : ?ComicSeries;
    function getAllPublished() : ComicSeriesCollection;
    function getPublishedByAlias(string $alias) : ?ComicSeries;
    function getAllByTag(string $tag, int $limit = 0) : ComicSeriesCollection;
}
