<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;

class EventRepository extends IdiormRepository implements EventRepositoryInterface
{
    use FullPublishedRepository;
    use ProtectedRepository;

    protected $entityClass = Event::class;

    public function getProtected(?int $id) : ?Event
    {
        return $this->getProtectedEntity($id);
    }
}
