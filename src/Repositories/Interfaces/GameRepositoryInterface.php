<?php

namespace App\Repositories\Interfaces;

use App\Models\Game;
use Plasticode\Collection;

interface GameRepositoryInterface
{
    function get(int $id) : ?Game;
    function getDefault() : ?Game;
    function getAllPublished() : Collection;
    function getPublishedByAlias(string $alias) : ?Game;
    function getByName(string $name) : ?Game;
    function getByTwitchName(string $name) : ?Game;
}
