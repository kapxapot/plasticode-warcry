<?php

namespace App\Repositories\Interfaces;

use App\Collections\StreamCollection;
use App\Models\Stream;

interface StreamRepositoryInterface
{
    function save(Stream $stream) : Stream;
    function getPublishedByAlias(string $alias) : ?Stream;
    function getAllPublished() : StreamCollection;

    function getAllByTag(
        string $tag,
        int $limit = 0
    ) : StreamCollection;
}
