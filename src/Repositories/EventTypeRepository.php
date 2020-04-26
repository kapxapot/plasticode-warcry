<?php

namespace App\Repositories;

use App\Models\EventType;
use App\Repositories\Interfaces\EventTypeRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class EventTypeRepository extends IdiormRepository implements EventTypeRepositoryInterface
{
    protected string $entityClass = EventType::class;

    public function get(?int $id) : ?EventType
    {
        return $this->getEntity($id);
    }
}
