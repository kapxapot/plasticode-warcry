<?php

namespace App\Repositories\Traits;

use App\Models\Game;
use Plasticode\Query;

trait ByGameRepository
{
    protected string $gameIdField = 'game_id';

    /**
     * Filters query by game if it isn't null.
     */
    protected function filterByGame(Query $query, ?Game $game) : Query
    {
        return $game
            ? $query->where($this->gameIdField, $game->getId())
            : $query;
    }

    /**
     * Filters query by game and all its sub-tree if it isn't null.
     */
    protected function filterByGameTree(Query $query, ?Game $game) : Query
    {
        return $game
            ? $query->whereIn(
                $this->gameIdField,
                $game->subTree()->ids()
            )
            : $query;
    }
}
