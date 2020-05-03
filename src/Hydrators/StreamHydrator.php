<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Stream;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\GameService;
use App\Services\StreamService;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class StreamHydrator extends Hydrator
{
    private GameRepositoryInterface $gameRepository;
    private UserRepositoryInterface $userRepository;

    private GameService $gameService;
    private StreamService $streamService;

    private LinkerInterface $linker;

    private TagsConfigInterface $tagsConfig;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        GameService $gameService,
        StreamService $streamService,
        LinkerInterface $linker,
        TagsConfigInterface $tagsConfig
    )
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;

        $this->gameService = $gameService;
        $this->streamService = $streamService;

        $this->linker = $linker;

        $this->tagsConfig = $tagsConfig;
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
                fn () =>
                $this->linker->tagLinks(
                    $entity,
                    $this->tagsConfig->getTab(get_class($entity))
                )
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }
}
