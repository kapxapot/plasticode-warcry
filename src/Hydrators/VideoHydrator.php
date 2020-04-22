<?php

namespace App\Hydrators;

use App\Models\Video;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class VideoHydrator extends Hydrator
{
    /**
     * @param Video $entity
     */
    public function hydrate(DbModel $entity) : Video
    {
        return $entity;
    }
}
