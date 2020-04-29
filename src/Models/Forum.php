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
}
