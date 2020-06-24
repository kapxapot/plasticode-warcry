<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use App\Models\Interfaces\NewsSourceInterface;
use App\Models\NewsSource;
use App\Repositories\Interfaces\NewsSourceRepositoryInterface;
use Plasticode\Util\Date;

abstract class NewsSourceRepositoryMock implements NewsSourceRepositoryInterface
{
    abstract protected function newsSources() : NewsSourceCollection;

    public function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return $this->getNewsByTag($tag, $limit);
    }

    public function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return $this
            ->newsSources()
            ->where(
                fn (NewsSource $n) => in_array($tag, $n->getTags())
            )
            ->take($limit);
    }

    public function getLatestNews(int $limit = 0, int $exceptId = 0) : NewsSourceCollection
    {
        return $this->getLatestNewsByGame(null, $limit, $exceptId);
    }

    public function getNewsCount() : int
    {
        return $this->getNewsCountByGame();
    }

    public function getNewsBefore(string $date, int $limit = 0) : NewsSourceCollection
    {
        return $this->getNewsBeforeByGame(null, $date, $limit);
    }

    public function getNewsAfter(string $date, int $limit = 0
    ) : NewsSourceCollection
    {
        return $this->getNewsAfterByGame(null, $date, $limit);
    }

    public function getNewsByYear(int $year) : NewsSourceCollection
    {
        return $this
            ->newsSources()
            ->where(
                fn (NewsSource $n) => Date::year($n->publishedAt) == $year
            );
    }

    public function getNews(?int $id) : ?NewsSourceInterface
    {
        return $this
            ->newsSources()
            ->first(
                fn (NewsSourceInterface $n) => $n->getId() == $id
            );
    }

    public function getLatestNewsByGame(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection
    {
        $col = $this->newsSources();

        if ($game) {
            $col = $col->where(
                fn (NewsSource $n) => $n->gameId == $game->getId()
            );
        }

        if ($exceptId > 0) {
            $col = $col->where(
                fn (NewsSource $n) => $n->getId() !== $exceptId
            );
        }

        return $col->take($limit);
    }

    public function getNewsCountByGame(?Game $game = null) : int
    {
        return $this
            ->getLatestNewsByGame($game)
            ->count();
    }

    public function getNewsBeforeByGame(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection
    {
        $col = $this->newsSources();

        if ($game) {
            $col = $col->where(
                fn (NewsSource $n) => $n->gameId == $game->getId()
            );
        }

        return $col
            ->where(
                fn (NewsSource $n) => Date::dt($date) < Date::dt($n->publishedAt)
            )
            ->take($limit);
    }

    public function getNewsAfterByGame(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection
    {
        $col = $this->newsSources();

        if ($game) {
            $col = $col->where(
                fn (NewsSource $n) => $n->gameId == $game->getId()
            );
        }

        return $col
            ->where(
                fn (NewsSource $n) => Date::dt($date) < Date::dt($n->publishedAt)
            )
            ->take($limit);
    }
}
