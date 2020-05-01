<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use App\Models\NewsSource;
use App\Repositories\Interfaces\NewsSourceRepositoryInterface;
use Plasticode\Util\Date;

abstract class NewsSourceRepositoryMock implements NewsSourceRepositoryInterface
{
    abstract protected function newsSources() : NewsSourceCollection;

    public function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return $this
            ->newsSources()
            ->where(
                fn (NewsSource $n) => in_array($tag, $n->getTags())
            )
            ->take($limit);
    }

    public function getLatestNews(
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

    public function getNewsBefore(
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

    public function getNewsAfter(
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

    public function getNewsByYear(int $year) : NewsSourceCollection
    {
        return $this
            ->newsSources()
            ->where(
                fn (NewsSource $n) => Date::year($n->publishedAt) == $year
            );
    }

    abstract function search(string $searchQuery) : NewsSourceCollection;
}
