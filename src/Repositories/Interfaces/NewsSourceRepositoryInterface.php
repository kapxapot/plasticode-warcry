<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Repositories\Interfaces\Basic\NewsSourceRepositoryInterface as BaseNewsSourceRepositoryInterface;

interface NewsSourceRepositoryInterface extends BaseNewsSourceRepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection;
    function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection;
    function getLatestNews(int $limit = 0, int $exceptId = 0) : NewsSourceCollection;
    function getNewsBefore(string $date, int $limit = 0) : NewsSourceCollection;
    function getNewsAfter(string $date, int $limit = 0) : NewsSourceCollection;
    function getNewsByYear(int $year) : NewsSourceCollection;
    function getNews(?int $id) : ?NewsSourceInterface;

    function getLatestNewsByGame(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection;

    function getNewsCountByGame(?Game $game = null) : int;

    function getNewsBeforeByGame(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection;

    function getNewsAfterByGame(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection;
}
