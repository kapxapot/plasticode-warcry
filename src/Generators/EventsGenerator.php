<?php

namespace App\Generators;

use App\Repositories\Interfaces\EventRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class EventsGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private EventRepositoryInterface $eventRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->eventRepository = $container->eventRepository;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);

        $data['cache'] = null;

        return $data;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $event = $this->eventRepository->get($id);

        $item['url'] = $event->url();

        return $item;
    }
}
