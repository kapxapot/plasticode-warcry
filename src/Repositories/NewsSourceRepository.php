<?php

namespace App\Repositories;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use App\Models\Interfaces\NewsSourceInterface;
use App\Repositories\Interfaces\NewsSourceRepositoryInterface;
use App\Repositories\Traits\ByGameRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;

abstract class NewsSourceRepository extends TaggedRepository implements NewsSourceRepositoryInterface
{
    use ByGameRepository;
    use FullPublishedRepository;
    use ProtectedRepository;

    protected string $sortField = 'published_at';
    protected bool $sortReverse = true;

    // NewsSourceRepositoryInterface

    public function getNewsByTag(
        string $tag,
        int $limit = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->newsSourceQuery()
                ->apply(
                    fn (Query $q) => $this->filterByTag($q, $tag, $limit)
                )
        );
    }

    public function getLatestNews(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->latestQuery($game, $limit, $exceptId)
        );
    }

    public function getNewsCount(?Game $game = null) : int
    {
        return $this
            ->latestQuery($game)
            ->count();
    }

    public function getNewsBefore(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->latestQuery($game, $limit)
                ->whereLt($this->publishedAtField, $date)
                ->orderByDesc($this->publishedAtField)
        );
    }

    public function getNewsAfter(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->latestQuery($game, $limit)
                ->whereGt($this->publishedAtField, $date)
                ->orderByAsc($this->publishedAtField)
        );
    }

    public function getNewsByYear(int $year) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->newsSourceQuery()
                ->whereRaw(
                    '(year(' . $this->publishedAtField . ') = ?)',
                    [$year]
                )
        );
    }

    public function getNews(?int $id) : ?NewsSourceInterface
    {
        return $this->getProtected($id);
    }

    abstract function getProtected(?int $id) : ?NewsSourceInterface;

    // queries

    protected function latestQuery(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : Query
    {
        return $this
            ->newsSourceQuery()
            ->apply(
                fn (Query $q) => $this->filterByGameTree($q, $game)
            )
            ->applyIf(
                $exceptId > 0,
                fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
            )
            ->limit($limit);
    }

    /**
     * Override this if needed.
     */
    protected function newsSourceQuery() : Query
    {
        return $this->publishedQuery();
    }
}
