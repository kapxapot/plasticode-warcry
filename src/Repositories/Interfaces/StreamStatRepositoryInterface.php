<?php

namespace App\Repositories\Interfaces;

use App\Models\StreamStat;

interface StreamStatRepositoryInterface
{
    function save(StreamStat $stream) : StreamStat;
}
