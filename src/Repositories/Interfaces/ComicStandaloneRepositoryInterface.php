<?php

namespace App\Repositories\Interfaces;

use App\Models\ComicStandalone;

interface ComicStandaloneRepositoryInterface
{
    function get(?int $id) : ?ComicStandalone;
    function getPublishedByAlias(string $alias) : ?ComicStandalone;
}
