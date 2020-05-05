<?php

namespace App\Repositories\Interfaces;

use App\Collections\StreamStatCollection;
use App\Models\Stream;
use App\Models\StreamStat;

interface StreamStatRepositoryInterface
{
    function save(StreamStat $stream) : StreamStat;
    function getLastByStream(Stream $stream) : ?StreamStat;

    function getLatestWithGameByStream(
        Stream $stream,
        int $days
    ) : StreamStatCollection;

    function getAllFromDateByStream(
        Stream $stream,
        \DateTime $from
    ) : StreamStatCollection;
}
