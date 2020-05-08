<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicSeriesCollection;
use App\Models\ComicSeries;

interface ComicSeriesRepositoryInterface
{
    function get(?int $id) : ?ComicSeries;
    function getAllPublished() : ComicSeriesCollection;
    function getPublishedByAlias(string $alias) : ?ComicSeries;
}
