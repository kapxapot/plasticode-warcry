<?php

namespace App\Hydrators;

use App\Models\Stream;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class StreamHydrator extends Hydrator
{
    /**
     * @param Stream $entity
     */
    public function hydrate(DbModel $entity) : Stream
    {
        return $entity;
    }
}
