<?php

namespace App\Collections;

use App\Models\Article;
use Plasticode\TypedCollection;

class ArticleCollection extends TypedCollection
{
    protected string $class = Article::class;
}
