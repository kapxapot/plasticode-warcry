<?php

namespace App\Repositories\Interfaces;

use App\Models\ArticleCategory;

interface ArticleCategoryRepositoryInterface
{
    function get(?int $id) : ?ArticleCategory;
    function getByName(string $name) : ?ArticleCategory;
}
