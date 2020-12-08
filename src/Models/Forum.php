<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Parented;

/**
 * @property integer $parentId
 * @method Game|null game()
 * @method bool isNewsForum()
 * @method static withGame(Game|callable|null $game)
 * @method static withIsNewsForum(bool|callable $isNewsForum)
 */
class Forum extends DbModel
{
    use Parented;

    protected function requiredWiths() : array
    {
        return [
            $this->parentPropertyName,
            'game',
            'isNewsForum',
        ];
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
            || $this->hasParent() && $this->parent()->belongsToGame($game);
    }
}
