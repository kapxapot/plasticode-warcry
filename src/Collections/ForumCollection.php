<?php

namespace App\Collections;

use App\Models\Forum;
use App\Models\Game;
use Plasticode\TypedCollection;

class ForumCollection extends TypedCollection
{
    protected string $class = Forum::class;

    /**
     * Filters forums by game.
     */
    public function byGame(Game $game) : self
    {
        $groups = $this->groupByGame();

        return self::from(
            $groups[$game->getId()] ?? []
        );
    }

    public function groupByGame() : array
    {
        return $this
            ->group(
                fn (Forum $f) => $f->gameId() ?? 0
            );
    }
}
