<?php

namespace App\Hydrators;

use App\Models\Game;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class GameHydrator extends Hydrator
{
    /**
     * @param Game $entity
     */
    public function hydrate(DbModel $entity) : Game
    {
        return $entity;
    }
}
