<?php

namespace App\Repositories;

use App\Models\ArticleCategory;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use Plasticode\Repositories\Idiorm\IdiormRepository;

class ArticleCategoryRepository extends IdiormRepository implements ArticleCategoryRepositoryInterface
{
    public function getByName(string $name) : ?ArticleCategory
    {
        return ArticleCategory::query()
            ->where('name_en', $name)
            ->one();
    }
}
