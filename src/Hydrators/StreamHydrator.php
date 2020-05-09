<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Stream;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\GameService;
use App\Services\StreamService;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class StreamHydrator extends ParsingHydrator
{
    private GameRepositoryInterface $gameRepository;
    private UserRepositoryInterface $userRepository;

    private GameService $gameService;
    private StreamService $streamService;

    private LinkerInterface $linker;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        GameService $gameService,
        StreamService $streamService,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct($parser);

        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;

        $this->gameService = $gameService;
        $this->streamService = $streamService;

        $this->linker = $linker;
    }

    /**
     * @param Stream $entity
     */
    public function hydrate(DbModel $entity) : Stream
    {
        return $entity
            ->withGame(
                fn () => $this->gameRepository->getByTwitchName($entity->remoteGame)
            )
            ->withIsAlive(
                fn () => $this->streamService->isAlive($entity)
            )
            ->withParsedDescription(
                fn () => $this->parse($entity->description)->text
            )
            ->withIsPriorityGame(
                fn () => $this->gameService->isPriorityGame($entity->remoteGame)
            )
            ->withImgUrl(
                fn () => $this->linker->twitchImg($entity->streamId)
            )
            ->withLargeImgUrl(
                fn () => $this->linker->twitchLargeImg($entity->streamId)
            )
            ->withPageUrl(
                fn () => $this->linker->stream($entity->alias())
            )
            ->withStreamUrl(
                fn () => $this->linker->twitch($entity->streamId)
            )
            ->withNouns(
                fn () => $this->streamService->nounsFor($entity)
            )
            ->withVerbs(
                fn () => $this->streamService->verbsFor($entity)
            )
            ->withTagLinks(
                fn () => $this->linker->tagLinks($entity)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }
}
