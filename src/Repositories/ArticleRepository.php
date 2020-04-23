<?php

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;
use App\Models\Game;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Traits\ByGameRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\ChildrenRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;

class ArticleRepository extends TaggedRepository implements ArticleRepositoryInterface
{
    use ByGameRepository;
    use ChildrenRepository;
    use FullPublishedRepository;
    use ProtectedRepository;

    protected string $entityClass = Article::class;

    protected string $sortField = 'published_at';
    protected bool $sortReverse = true;

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

    public function getLatest(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : ArticleCollection
    {
        return ArticleCollection::from(
            $this->latestQuery($game, $limit, $exceptId)
        );
    }

    protected function latestQuery(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : Query
    {
        $query = $this->announcedQuery();

        if ($exceptId > 0) {
            $query = $query->whereNotEqual(
                $this->idField(),
                $exceptId
            );
        }

        return $this
            ->filterByGame($query, $game)
            ->limit($limit);
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

    public function getAllByTag(
        string $tag,
        int $limit = 0
    ) : ArticleCollection
    {
        return ArticleCollection::from(
            $this->byTagQuery(
                $this->announcedQuery(),
                $tag,
                $limit
            )
        );
    }

    public function getAllBefore(
        ?Game $game,
        string $date,
        int $limit = 0
    ) : ArticleCollection
    {
        return ArticleCollection::from(
            $this
                ->latestQuery($game, $limit)
                ->whereLt($this->publishedAtField, $date)
                ->orderByDesc($this->publishedAtField)
        );
    }
    
    public function getAllAfter(
        ?Game $game,
        string $date,
        int $limit = 0
    ) : ArticleCollection
    {
        return ArticleCollection::from(
            $this
                ->latestQuery($game, $limit)
                ->whereGt($this->publishedAtField, $date)
                ->orderByAsc($this->publishedAtField)
        );
    }
    
    public function getAllByYear(int $year) : ArticleCollection
    {
        return ArticleCollection::from(
            $this
                ->announcedQuery()
                ->whereRaw(
                    '(year(' . $this->publishedAtField . ') = ?)',
                    [$year]
                )
        );
    }

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
