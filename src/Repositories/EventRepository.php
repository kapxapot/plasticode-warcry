<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\ProtectedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublish;

class EventRepository extends ProtectedRepository implements EventRepositoryInterface
{
    use FullPublish;

    protected $entityClass = Event::class;

    public function getProtected(int $id) : ?Event
    {
        return $this->getProtectedEntity($id);
    }
}
