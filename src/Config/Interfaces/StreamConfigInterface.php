<?php

namespace App\Config\Interfaces;

interface StreamConfigInterface
{
    /**
     * Stream time to live in days.
     */
    function streamTimeToLive() : int;

    /**
     * Priority games list for streams.
     * 
     * @return string[]
     */
    function streamPriorityGames() : array;

    /**
     * Stream analysis period in days.
     */
    function streamAnalysisPeriod() : int;
}
