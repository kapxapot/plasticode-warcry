<?php

namespace App\Collections;

use App\Models\ArticleCategory;
use Plasticode\TypedCollection;

class ArticleCategoryCollection extends TypedCollection
{
    protected string $class = ArticleCategory::class;
}
