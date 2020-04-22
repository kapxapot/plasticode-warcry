<?php

namespace App\Hydrators;

use App\Models\Event;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class EventHydrator extends Hydrator
{
    /**
     * @param Event $entity
     */
    public function hydrate(DbModel $entity) : Event
    {
        return $entity;
    }
}
