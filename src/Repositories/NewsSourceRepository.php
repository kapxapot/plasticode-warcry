<?php

namespace App\Repositories;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
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

    protected function newsSourceQuery() : Query
    {
        return $this->publishedQuery();
    }

    protected function latestQuery(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : Query
    {
        $query = $this->newsSourceQuery();

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

    public function getAllByTag(
        string $tag,
        int $limit = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->byTagQuery(
                $this->newsSourceQuery(),
                $tag,
                $limit
            )
        );
    }

    public function getLatest(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->latestQuery($game, $limit, $exceptId)
        );
    }

    public function getAllBefore(
        ?Game $game,
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

    public function getAllAfter(
        ?Game $game,
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

    public function getAllByYear(int $year) : NewsSourceCollection
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
}
