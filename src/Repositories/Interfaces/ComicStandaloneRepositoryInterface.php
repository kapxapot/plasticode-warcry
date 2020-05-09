<?php

namespace App\Repositories\Interfaces;

use App\Collections\ComicStandaloneCollection;
use App\Models\ComicStandalone;

interface ComicStandaloneRepositoryInterface
{
    function get(?int $id) : ?ComicStandalone;
    function getPublishedByAlias(string $alias) : ?ComicStandalone;
    function getAllByTag(string $tag, int $limit = 0) : ComicStandaloneCollection;
}
