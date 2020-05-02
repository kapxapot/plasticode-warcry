<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\Menu as MenuBase;

class Menu extends MenuBase
{
    public static function getByGame($gameId) : Query
    {
        return self::query()
            ->where('game_id', $gameId);
    }

    public function game()
    {
        return Game::get($this->gameId);
    }
}
