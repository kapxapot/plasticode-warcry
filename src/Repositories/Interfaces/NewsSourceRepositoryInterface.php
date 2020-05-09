<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Repositories\Interfaces\Basic\TaggedRepositoryInterface;

interface NewsSourceRepositoryInterface extends TaggedRepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection;

    function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection;

    function getLatestNews(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection;

    function getNewsCount(?Game $game = null) : int;

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

    function getNews(?int $id) : ?NewsSourceInterface;
}
