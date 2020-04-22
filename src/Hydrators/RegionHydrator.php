<?php

namespace App\Hydrators;

use App\Models\Region;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class RegionHydrator extends Hydrator
{
    /**
     * @param Region $entity
     */
    public function hydrate(DbModel $entity) : Region
    {
        return $entity;
    }
}
