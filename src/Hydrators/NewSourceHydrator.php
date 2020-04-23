<?php

namespace App\Hydrators;

use App\Models\NewsSource;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

abstract class NewsSourceHydrator extends Hydrator
{
    protected GameRepositoryInterface $gameRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository
    )
    {
        $this->gameRepository = $gameRepository;
    }

    /**
     * @param NewsSource $entity
     */
    public function hydrate(DbModel $entity) : NewsSource
    {
        return $entity
            ->withGame(
                $this->gameRepository->get($entity->gameId)
            );
    }
}
