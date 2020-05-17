<?php

namespace App\Config\Interfaces;

interface GameConfigInterface
{
    /**
     * Default game id, null by default.
     */
    function defaultGameId() : ?int;

    /**
     * Priority games list for streams.
     */
    function streamPriorityGames() : array;
}
