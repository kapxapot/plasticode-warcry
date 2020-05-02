<?php

namespace App\Repositories\Interfaces;

use App\Collections\GameCollection;
use App\Models\Forum;
use App\Models\Game;

interface GameRepositoryInterface
{
    function get(?int $id) : ?Game;
    function getDefault() : ?Game;
    function getAll() : GameCollection;
    function getAllPublished() : GameCollection;
    function getPublishedByAlias(string $alias) : ?Game;
    function getByName(?string $name) : ?Game;
    function getByTwitchName(?string $name) : ?Game;

    /**
     * Returns game by forum (going up in the forum tree).
     * If not found returns the default game.
     */
    function getByForum(Forum $forum) : ?Game;

    /**
     * Returns game's sub-tree or all games (if the game is null).
     */
    function getSubTreeOrAll(?Game $game) : GameCollection;
}
