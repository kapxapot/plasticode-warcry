<?php

namespace App\Config\Interfaces;

interface GameConfigInterface
{
    /**
     * Default game id, null by default.
     *
     * @return integer|null
     */
    public function defaultGameId() : ?int;
    
    /**
     * Priority games list for streams.
     * 
     * @return string[]
     */
    public function streamPriorityGames() : array;
}
