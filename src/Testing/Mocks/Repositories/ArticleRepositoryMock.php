<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;
use Plasticode\Util\Strings;

class ArticleRepositoryMock implements ArticleRepositoryInterface
{
    private ArticleCategoryRepositoryInterface $articleCategoryRepository;
    private ArticleCollection $articles;

    public function __construct(
        ArticleCategoryRepositoryInterface $articleCategoryRepository,
        ArraySeederInterface $seeder
    )
    {
        $this->articleCategoryRepository = $articleCategoryRepository;

        $this->articles = ArticleCollection::make($seeder->seed());
    }

    public function getBySlugOrAlias(string $slug, string $cat = null) : ?Article
    {
        return
            $this->getBySlug($slug, $cat)
            ??
            $this->getByAlias($slug, $cat);
    }

    public function getBySlug(string $slug, string $cat = null) : ?Article
    {
        $slug = Strings::toSpaces($slug);
        $cat = Strings::toSpaces($cat);

        $query = $this
            ->articles
            //->protectedQuery()
            ->where('name_en', $slug);

        if (strlen($cat) > 0) {
            $category = $this->articleCategoryRepository->getByName($cat);
            
            if ($category) {
                return $query
                    ->where(
                        function (Article $article) use ($category) {
                            $cat = $article->cat;
                            return is_null($cat) || $cat == $category->getId();
                        }
                    )
                    ->desc('cat')
                    ->first();
            }
        }

        return $query
            ->asc('cat')
            ->first();
    }

    public function getByAlias(string $name, string $cat = null) : ?Article
    {
        $name = Strings::toSpaces($name);
        $cat = Strings::toSpaces($cat);

        $aliasParts[] = $name;
        
        if (strlen($cat) > 0) {
            $aliasParts[] = $cat;
        }
        
        $alias = Strings::joinTagParts($aliasParts);

        return $this
            ->articles
            //->protectedQuery()
            ->where(
                function (Article $article) use ($alias) {
                    return Strings::contains($article->aliases, $alias);
                }
            )
            ->first();
    }
}
