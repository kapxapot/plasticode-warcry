<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;
use Plasticode\Util\Strings;

class ArticleRepository extends IdiormRepository implements ArticleRepositoryInterface
{
    use FullPublishedRepository;
    use ProtectedRepository;

    protected $entityClass = Article::class;

    public function getBySlugOrAlias(string $slug, string $cat = null) : ?Article
    {
        return
            $this->getBySlug($slug, $cat)
            ??
            $this->getByAlias($slug, $cat);
    }

    public function getBySlug(string $slug, string $cat = null) : ?Article
    {
        return $this
            ->getAllBySlugQuery($slug, $cat)
            ->one();
    }

    protected function getAllBySlugQuery(string $slug, string $cat = null) : Query
    {
        $slug = Strings::toSpaces($slug);
        $cat = Strings::toSpaces($cat);

        $query = $this
            ->protectedQuery()
            ->where('name_en', $slug);
    
        if (strlen($cat) > 0) {
            $category = $this->articleCategoryRepository->getByName($cat);
            
            if ($category) {
                return $query
                    ->whereRaw('(cat = ? or cat is null)', [$category->id])
                    ->orderByDesc('cat');
            }
        }

        return $query->orderByAsc('cat');
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
            ->protectedQuery()
            ->whereRaw('(aliases like ?)', ['%' . $alias . '%'])
            ->one();
    }
}
