<?php

namespace App\Hydrators;

use App\Models\ArticleCategory;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ArticleCategoryHydrator extends Hydrator
{
    /**
     * @param ArticleCategory $entity
     */
    public function hydrate(DbModel $entity) : ArticleCategory
    {
        return $entity;
    }
}
