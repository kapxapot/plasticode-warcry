<?php

namespace App\Models;

use App\Collections\MenuItemCollection;
use Plasticode\Models\Menu as MenuBase;

/**
 * @property integer $gameId
 * @method Game game()
 * @method MenuItemCollection items()
 * @method static withGame(Game|callable $game)
 * @method static withItems(MenuItemCollection|callable $items)
 */
class Menu extends MenuBase
{
    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            'game',
        ];
    }
}
