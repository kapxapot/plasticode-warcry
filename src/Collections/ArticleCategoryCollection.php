<?php

namespace App\Collections;

use App\Models\ArticleCategory;
use Plasticode\Collections\Basic\DbModelCollection;

class ArticleCategoryCollection extends DbModelCollection
{
    protected string $class = ArticleCategory::class;
}
