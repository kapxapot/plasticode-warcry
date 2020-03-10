<?php

namespace App\Config\Interfaces;

interface StreamConfigInterface
{
    /**
     * Stream time to live in days.
     *
     * @return integer
     */
    public function streamTimeToLive() : int;

    /**
     * Priority games list for streams.
     * 
     * @return string[]
     */
    public function streamPriorityGames() : array;
}
