<?php

namespace App\Collections;

use App\Models\Article;

class ArticleCollection extends NewsSourceCollection
{
    protected string $class = Article::class;
}
