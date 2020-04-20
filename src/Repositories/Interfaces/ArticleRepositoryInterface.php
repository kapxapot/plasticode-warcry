<?php

namespace App\Repositories\Interfaces;

use App\Models\Article;

interface ArticleRepositoryInterface
{
    function getBySlugOrAlias(string $name, string $cat = null) : ?Article;
    function getBySlug(string $slug, string $cat = null) : ?Article;
    function getByAlias(string $name, string $cat = null) : ?Article;
}
