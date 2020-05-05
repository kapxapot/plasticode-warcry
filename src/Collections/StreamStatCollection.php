<?php

namespace App\Collections;

use App\Models\StreamStat;
use Plasticode\Collections\Basic\DbModelCollection;

class StreamStatCollection extends DbModelCollection
{
    protected string $class = StreamStat::class;

    /**
     * @return array<string, static>
     */
    public function groupByGame() : array
    {
        return $this
            ->group(
                fn (StreamStat $s) => $s->remoteGame
            );
    }
}
