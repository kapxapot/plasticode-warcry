<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use Plasticode\Repositories\Interfaces\SearchableRepositoryInterface;

interface NewsSourceRepositoryInterface extends SearchableRepositoryInterface
{
    function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection;

    function getLatestNews(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection;

    function getNewsBefore(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection;

    function getNewsAfter(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection;

    function getNewsByYear(int $year) : NewsSourceCollection;
}
