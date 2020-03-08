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
}
