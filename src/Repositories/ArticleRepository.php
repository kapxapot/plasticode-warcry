<?php

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;
use App\Models\Game;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Traits\ByGameRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\ChildrenRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;
use Plasticode\Util\Strings;

class ArticleRepository extends IdiormRepository implements ArticleRepositoryInterface
{
    use ByGameRepository;
    use ChildrenRepository;
    use FullPublishedRepository;
    use ProtectedRepository;

    protected string $entityClass = Article::class;

    protected string $sortField = $this->publishedAtField;
    protected bool $sortReverse = true;

    public function getBySlugOrAlias(string $slug, string $cat = null) : ?Article
    {
        return
            $this->getBySlug($slug, $cat)
            ?? $this->getByAlias($slug, $cat);
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

    public function getAllPublishedOrphans() : ArticleCollection
    {
        return ArticleCollection::from(
            $this->orphansQuery(
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
            $this->getLatestQuery($game, $limit, $exceptId)
        )
    }

    protected function getLatestQuery(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : Query
    {
        $query = $this->filterAnnounced(
            $this->publishedQuery()
        );

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
        $query = $this->query()
            ->where('name_en', $name);

        if ($catId > 0) {
            $query = $query->where('cat', $catId);
        } else {
            $query = $query->whereNull('cat');
        }

        if ($exceptId > 0) {
            $query = $query->whereNotEqual(
                $this->idField(),
                $exceptId
            );
        }

        return ArticleCollection::from($query);
    }
}
