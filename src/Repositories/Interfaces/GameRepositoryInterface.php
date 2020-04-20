<?php

namespace App\Repositories\Interfaces;

use App\Collections\GameCollection;
use App\Models\Game;

interface GameRepositoryInterface
{
    function get(?int $id) : ?Game;
    function getDefault() : ?Game;
    function getAllPublished() : GameCollection;
    function getPublishedByAlias(string $alias) : ?Game;
    function getByName(string $name) : ?Game;
    function getByTwitchName(string $name) : ?Game;
}
