<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property integer $parentId
 * @method Game|null game()
 * @method bool isNewsForum()
 * @method static|null parent()
 * @method static withGame(Game|callable|null $game)
 * @method static withIsNewsForum(bool|callable $isNewsForum)
 * @method static withParent(static|callable|null $parent)
 */
class Forum extends DbModel
{
    protected function requiredWiths(): array
    {
        return ['game', 'parent', 'isNewsForum'];
    }

    public function gameId() : ?int
    {
        return $this->game()
            ? $this->game()->getId()
            : null;
    }

    /**
     * Checks if the current forum or its parent belongs to game.
     */
    public function belongsToGame(Game $game) : bool
    {
        return $game->relatesToForum($this)
            || $this->parent() && $this->parent()->belongsToGame($game);
    }
}
