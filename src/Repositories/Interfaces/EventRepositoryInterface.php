<?php

namespace App\Repositories\Interfaces;

use App\Collections\EventCollection;
use App\Models\Event;
use App\Models\Game;

interface EventRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    function get(?int $id) : ?Event;
    function getProtected(?int $id) : ?Event;
    function getAllOrderedByStart() : EventCollection;
    function getAllUnended() : EventCollection;
    function getAllCurrent(?Game $game, int $days) : EventCollection;
    function getAllFutureImportant() : EventCollection;

    function search(string $searchQuery) : EventCollection;
}
