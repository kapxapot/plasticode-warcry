<?php

namespace App\Models;

use Plasticode\Models\Menu as MenuBase;

class Menu extends MenuBase
{
    // GETTERS - MANY
    
    public static function getAllByGame($gameId)
    {
        return self::getAllByField('game_id', $gameId);
    }
}
