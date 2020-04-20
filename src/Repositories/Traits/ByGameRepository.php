<?php

namespace App\Repositories\Traits;

use App\Models\Game;
use Plasticode\Query;

trait ByGameRepository
{
    protected string $gameIdField = 'game_id';

    protected function filterByGame(Query $query, ?Game $game = null) : Query
    {
        return $game
            ? $query->where($this->gameIdField, $game->getId())
            : $query;
    }
}
