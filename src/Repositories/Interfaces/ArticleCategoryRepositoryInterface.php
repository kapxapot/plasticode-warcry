<?php

namespace App\Repositories\Interfaces;

use App\Models\ArticleCategory;

interface ArticleCategoryRepositoryInterface
{
    public function getByName(string $name) : ?ArticleCategory;
}
