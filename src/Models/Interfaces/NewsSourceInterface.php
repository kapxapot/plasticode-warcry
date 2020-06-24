<?php

namespace App\Models\Interfaces;

use App\Models\Game;
use App\Models\User;
use Plasticode\Models\Interfaces\NewsSourceInterface as BaseNewsSourceInterface;

interface NewsSourceInterface extends BaseNewsSourceInterface
{
    function game() : ?Game;
    function rootGame() : ?Game;

    function creator() : ?User;
}
