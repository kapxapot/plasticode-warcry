<?php

namespace App\Repositories;

use App\Models\ArticleCategory;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ArticleCategoryRepository extends IdiormRepository implements ArticleCategoryRepositoryInterface
{
    protected string $entityClass = ArticleCategory::class;

    public function get(?int $id) : ?ArticleCategory
    {
        return $this->getEntity($id);
    }

    public function getByName(string $name) : ?ArticleCategory
    {
        return $this
            ->query()
            ->where('name_en', $name)
            ->one();
    }
}
