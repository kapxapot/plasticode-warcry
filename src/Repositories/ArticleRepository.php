<?php

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Traits\ChildrenRepository;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;

class ArticleRepository extends NewsSourceRepository implements ArticleRepositoryInterface
{
    use ChildrenRepository;

    protected string $entityClass = Article::class;

    public function get(?int $id) : ?Article
    {
        return $this->getEntity($id);
    }

    public function getBySlugOrAlias(string $slug, string $cat = null) : ?Article
    {
        return
            $this->getBySlug($slug, $cat)
            ?? $this->getByAlias($slug, $cat);
    }

    public function getBySlug(string $slug, string $cat = null) : ?Article
    {
        return $this
            ->bySlugQuery($slug, $cat)
            ->one();
    }

    protected function bySlugQuery(string $slug, string $cat = null) : Query
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

    public function getChildren(Article $parent) : ArticleCollection
    {
        return ArticleCollection::from(
            $this->filterByParent(
                $this->query(),
                $parent->getId()
            )
        );
    }

    /**
     * Returns all published orphans.
     */
    public function getAllOrphans() : ArticleCollection
    {
        return ArticleCollection::from(
            $this->filterOrphans(
                $this->publishedQuery()
            )
        );
    }

    /**
     * Published + announced query.
     */
    protected function announcedQuery() : Query
    {
        return $this->filterAnnounced(
            $this->publishedQuery()
        );
    }

    protected function filterAnnounced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }

    /**
     * Check article duplicates for validation.
     */
    public function lookup(
        string $name,
        int $catId = 0,
        int $exceptId = 0
    ) : ArticleCollection
    {
        $query = $this
            ->query()
            ->where('name_en', $name);

        if ($catId > 0) {
            $query = $query->where('cat', $catId);
        } else {
            $query = $query->whereNull('cat');
        }

        if ($exceptId > 0) {
            $query = $query
                ->whereNotEqual(
                    $this->idField(),
                    $exceptId
                );
        }

        return ArticleCollection::from($query);
    }

    // NewsSourceRepositoryInterface

    protected function newsSourceQuery() : Query
    {
        return $this->announcedQuery();
    }

    // SearchableRepositoryInterface

    public function search(string $searchQuery) : ArticleCollection
    {
        return ArticleCollection::from(
            $this
                ->publishedQuery()
                ->search($searchQuery, '(name_en like ? or name_ru like ?)', 2)
                ->all()
                ->multiSort(
                    [
                        SortStep::createByField('name_ru')
                            ->withType(Sort::STRING),
                        SortStep::createByField('category')
                            ->withType(Sort::NULL),
                    ]
                )
        );
    }
}
