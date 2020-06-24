<?php

namespace App\Repositories;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use App\Models\Interfaces\NewsSourceInterface;
use App\Repositories\Interfaces\NewsSourceRepositoryInterface;
use App\Repositories\Traits\ByGameRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\NewsSourceRepository as BaseNewsSourceRepository;

abstract class NewsSourceRepository extends BaseNewsSourceRepository implements NewsSourceRepositoryInterface
{
    use ByGameRepository;

    // TaggedRepositoryInterface

    public function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            parent::getAllByTag($tag, $limit)
        );
    }

    // base NewsSourceRepositoryInterface

    public function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            parent::getNewsByTag($tag, $limit)
        );
    }

    public function getLatestNews(int $limit = 0, int $exceptId = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            parent::getLatestNews($limit, $exceptId)
        );
    }

    public function getNewsBefore(string $date, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            parent::getNewsBefore($date, $limit)
        );
    }

    public function getNewsAfter(string $date, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            parent::getNewsAfter($date, $limit)
        );
    }

    public function getNewsByYear(int $year) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            parent::getNewsByYear($year)
        );
    }

    public function getNews(?int $id) : ?NewsSourceInterface
    {
        return $this->getProtected($id);
    }

    abstract function getProtected(?int $id) : ?NewsSourceInterface;

    // NewsSourceRepositoryInterface

    public function getLatestNewsByGame(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->latestByGameQuery($game, $limit, $exceptId)
        );
    }

    public function getNewsCountByGame(?Game $game = null) : int
    {
        return $this
            ->latestByGameQuery($game)
            ->count();
    }

    public function getNewsBeforeByGame(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->latestByGameQuery($game, $limit)
                ->whereLt($this->publishedAtField, $date)
                ->orderByDesc($this->publishedAtField)
        );
    }

    public function getNewsAfterByGame(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->latestByGameQuery($game, $limit)
                ->whereGt($this->publishedAtField, $date)
                ->orderByAsc($this->publishedAtField)
        );
    }

    // queries

    protected function latestByGameQuery(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : Query
    {
        return $this
            ->latestQuery($limit, $exceptId)
            ->apply(
                fn (Query $q) => $this->filterByGameTree($q, $game)
            );
    }
}
