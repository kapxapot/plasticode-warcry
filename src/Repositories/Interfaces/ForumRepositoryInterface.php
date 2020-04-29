<?php

namespace App\Repositories\Interfaces;

use App\Collections\ForumCollection;
use App\Models\Forum;
use App\Models\Game;

interface ForumRepositoryInterface
{
    function get(?int $id) : ?Forum;
    function getParent(Forum $forum) : ?Forum;
    function getAll() : ForumCollection;
    function getAllByGame(Game $game) : ForumCollection;
}
