<?php

namespace App\Repositories;

use App\Models\Stream;
use App\Repositories\Interfaces\StreamRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class StreamRepository extends IdiormRepository implements StreamRepositoryInterface
{
    protected $entityClass = Stream::class;

    public function save(Stream $stream) : Stream
    {
        return $this->saveEntity($stream);
    }
}
