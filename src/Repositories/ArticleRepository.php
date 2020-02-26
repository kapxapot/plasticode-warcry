<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Repositories\IdiormRepository;

class ArticleRepository extends IdiormRepository implements ArticleRepositoryInterface
{
    public function getBySlugOrAlias(string $slug, string $cat = null) : ?Article
    {
        return Article::getByName($slug, $cat) ?? Article::getByAlias($slug, $cat);
    }
}
