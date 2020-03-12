<?php

namespace App\Repositories;

use App\Models\StreamStat;
use App\Repositories\Interfaces\StreamStatRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class StreamStatRepository extends IdiormRepository implements StreamStatRepositoryInterface
{
    protected $entityClass = StreamStat::class;

    public function save(StreamStat $stat) : StreamStat
    {
        return $this->saveEntity($stat);
    }
}
