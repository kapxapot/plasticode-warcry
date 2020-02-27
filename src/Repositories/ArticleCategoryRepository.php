<?php

namespace App\Repositories;

use App\Models\ArticleCategory;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;

class ArticleCategoryRepository implements ArticleCategoryRepositoryInterface
{
    public function getByName(string $name) : ?ArticleCategory
    {
        return ArticleCategory::query()
            ->where('name_en', $name)
            ->one();
    }
}
