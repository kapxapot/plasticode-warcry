<?php

namespace App\Collections;

use App\Models\Game;
use Plasticode\TypedCollection;

class GameCollection extends TypedCollection
{
    protected string $class = Game::class;
}
