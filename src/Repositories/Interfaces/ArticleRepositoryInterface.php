<?php

namespace App\Repositories\Interfaces;

use App\Models\Article;

interface ArticleRepositoryInterface
{
    public function getBySlugOrAlias(string $name, string $cat = null) : ?Article;
}
