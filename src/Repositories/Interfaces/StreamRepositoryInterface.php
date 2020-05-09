<?php

namespace App\Repositories\Interfaces;

use App\Collections\StreamCollection;
use App\Models\Stream;
use Plasticode\Repositories\Interfaces\Basic\TaggedRepositoryInterface;

interface StreamRepositoryInterface extends TaggedRepositoryInterface
{
    function save(Stream $stream) : Stream;
    function getPublishedByAlias(string $alias) : ?Stream;
    function getAllPublished() : StreamCollection;

    function getAllByTag(
        string $tag,
        int $limit = 0
    ) : StreamCollection;
}
