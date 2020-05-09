<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Event;
use App\Repositories\Interfaces\EventTypeRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\RegionRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;

class EventHydrator extends NewsSourceHydrator
{
    private EventTypeRepositoryInterface $eventTypeRepository;
    private RegionRepositoryInterface $regionRepository;

    public function __construct(
        EventTypeRepositoryInterface $eventTypeRepository,
        GameRepositoryInterface $gameRepository,
        RegionRepositoryInterface $regionRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct(
            $gameRepository,
            $userRepository,
            $cutParser,
            $linker,
            $parser
        );

        $this->eventTypeRepository = $eventTypeRepository;
        $this->regionRepository = $regionRepository;
    }

    /**
     * @param Event $entity
     */
    public function hydrate(DbModel $entity) : Event
    {
        /** @var Event */
        $entity = parent::hydrate($entity);

        return $entity
            ->withRegion(
                fn () => $this->regionRepository->get($entity->regionId)
            )
            ->withType(
                fn () => $this->eventTypeRepository->get($entity->typeId)
            )
            ->withUrl(
                fn () => $this->linker->event($entity->getId())
            );
    }
}
