<?php

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Idiorm\Traits\ChildrenRepository;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;

class ArticleRepository extends NewsSourceRepository implements ArticleRepositoryInterface
{
    use ChildrenRepository;

    protected string $entityClass = Article::class;

    protected ArticleCategoryRepositoryInterface $articleCategoryRepository;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $repositoryContext,
        ArticleCategoryRepositoryInterface $articleCategoryRepository,
        TagRepositoryInterface $tagRepository,
        $hydrator = null
    )
    {
        parent::__construct(
            $repositoryContext,
            $tagRepository,
            $hydrator
        );

        $this->articleCategoryRepository = $articleCategoryRepository;
    }

    public function get(?int $id) : ?Article
    {
        return $this->getEntity($id);
    }

    public function getProtected(?int $id) : ?Article
    {
        return $this->getProtectedEntity($id);
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
            $this
                ->query()
                ->apply(
                    fn (Query $q) => $this->filterByParent($q, $parent->getId())
                )
        );
    }

    /**
     * Returns all published orphans.
     */
    public function getAllOrphans() : ArticleCollection
    {
        return ArticleCollection::from(
            $this
                ->publishedQuery()
                ->apply(
                    fn (Query $q) => $this->filterOrphans($q)
                )
        );
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
        return ArticleCollection::from(
            $this
                ->query()
                ->where('name_en', $name)
                ->applyIfElse(
                    $catId > 0,
                    fn (Query $q) => $q->where('cat', $catId),
                    fn (Query $q) => $q->whereNull('cat')
                )
                ->applyIf(
                    $exceptId > 0,
                    fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
                )
        );
    }

    // SearchableRepositoryInterface

    public function search(string $searchQuery) : ArticleCollection
    {
        return ArticleCollection::from(
            $this
                ->publishedQuery()
                ->search($searchQuery, '(name_en like ? or name_ru like ?)', 2)
                ->all()
                ->sortBy(
                    SortStep::byField('name_ru', Sort::STRING),
                    SortStep::byField('category', Sort::NULL)
                )
        );
    }

    // queries

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
                    ->whereRaw(
                        '(cat = ? or cat is null)',
                        [$category->getId()]
                    )
                    ->orderByDesc('cat');
            }
        }

        return $query->orderByAsc('cat');
    }

    protected function newsSourceQuery() : Query
    {
        return $this->announcedQuery();
    }

    /**
     * Published + announced query.
     */
    protected function announcedQuery() : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterAnnounced($q)
            );
    }

    // filters

    protected function filterAnnounced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
}
