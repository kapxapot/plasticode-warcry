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

    public function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return $this
            ->newsSources()
            ->where(
                fn (NewsSource $n) => in_array($tag, $n->getTags())
            )
            ->take($limit);
    }

    public function getLatest(
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

    public function getAllByYear(int $year) : NewsSourceCollection
    {
        return $this
            ->newsSources()
            ->where(
                fn (NewsSource $n) => Date::year($n->publishedAt) == $year
            );
    }

    public function getAllBefore(
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

    public function getAllAfter(
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

    abstract function search(string $searchQuery) : NewsSourceCollection;
}
