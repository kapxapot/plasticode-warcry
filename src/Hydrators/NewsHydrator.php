<?php

namespace App\Hydrators;

use App\Models\News;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class NewsHydrator extends Hydrator
{
    /**
     * @param News $entity
     */
    public function hydrate(DbModel $entity) : News
    {
        return $entity;
    }
}
