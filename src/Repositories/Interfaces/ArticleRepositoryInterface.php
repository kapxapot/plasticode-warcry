<?php

namespace App\Repositories\Interfaces;

use App\Models\Article;

interface ArticleRepositoryInterface
{
    public function getBySlugOrAlias(string $name, string $cat = null) : ?Article;
    public function getBySlug(string $slug, string $cat = null) : ?Article;
    public function getByAlias(string $name, string $cat = null) : ?Article;
}
