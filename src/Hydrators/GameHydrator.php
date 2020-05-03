<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Game;
use App\Repositories\Interfaces\ForumRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\GameService;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class GameHydrator extends Hydrator
{
    private ForumRepositoryInterface $forumRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;

    private GameService $gameService;

    public function __construct(
        ForumRepositoryInterface $forumRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker,
        GameService $gameService
    )
    {
        $this->forumRepository = $forumRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;

        $this->gameService = $gameService;
    }

    /**
     * @param Game $entity
     */
    public function hydrate(DbModel $entity) : Game
    {
        return $entity
            ->withParent(
                fn () => $this->gameRepository->get($entity->parentId)
            )
            ->withChildren(
                fn () => $this->gameRepository->getChildren($entity)
            )
            ->withMainForum(
                fn () => $this->forumRepository->get($entity->mainForumId)
            )
            ->withNewsForum(
                fn () => $this->forumRepository->get($entity->newsForumId)
            )
            ->withForums(
                fn () => $this->forumRepository->getAllByGame($entity)
            )
            ->withResultAlias(
                fn () => $this->gameService->resultAlias($entity)
            )
            ->withResultIcon(
                fn () => $this->gameService->resultIcon($entity)
            )
            ->withIsDefault(
                fn () => $entity->equals($this->gameRepository->getDefault())
            )
            ->withUrl(
                fn () => $this->linker->game($entity)
            );
    }
}
