<?php

namespace App\Hydrators;

use App\Models\Article;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ArticleHydrator extends Hydrator
{
    /**
     * @param Article $entity
     */
    public function hydrate(DbModel $entity) : Article
    {
        return $entity;
    }
}
