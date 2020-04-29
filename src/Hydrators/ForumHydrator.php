<?php

namespace App\Hydrators;

use App\Models\Forum;
use App\Repositories\Interfaces\ForumRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\ForumService;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ForumHydrator extends Hydrator
{
    private ForumRepositoryInterface $forumRepository;
    private GameRepositoryInterface $gameRepository;

    private ForumService $forumService;

    public function __construct(
        ForumRepositoryInterface $forumRepository,
        GameRepositoryInterface $gameRepository,
        ForumService $forumService
    )
    {
        $this->forumRepository = $forumRepository;
        $this->gameRepository = $gameRepository;

        $this->forumService = $forumService;
    }

    /**
     * @param Forum $entity
     */
    public function hydrate(DbModel $entity) : Forum
    {
        return $entity
            ->withGame(
                fn () => $this->gameRepository->getByForum($entity)
            )
            ->withParent(
                fn () => $this->forumRepository->getParent($entity)
            )
            ->withIsNewsForum(
                fn () =>
                $this
                    ->forumService
                    ->getNewsForums()
                    ->any(
                        fn (Forum $f) => $f->equals($entity)
                    )
            );
    }
}
