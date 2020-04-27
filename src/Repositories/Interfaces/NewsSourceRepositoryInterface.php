<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsSourceCollection;
use App\Models\Game;
use Plasticode\Repositories\Interfaces\SearchableRepositoryInterface;

interface NewsSourceRepositoryInterface extends SearchableRepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection;

    function getLatest(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : NewsSourceCollection;

    function getAllByYear(int $year) : NewsSourceCollection;

    function getAllBefore(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection;

    function getAllAfter(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : NewsSourceCollection;
}
