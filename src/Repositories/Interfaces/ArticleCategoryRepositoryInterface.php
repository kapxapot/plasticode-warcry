<?php

namespace App\Repositories\Interfaces;

use App\Models\ArticleCategory;

interface ArticleCategoryRepositoryInterface
{
    function getByName(string $name) : ?ArticleCategory;
}
