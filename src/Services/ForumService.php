<?php

namespace App\Services;

use App\Collections\ForumCollection;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;

class ForumService
{
    private GameRepositoryInterface $gameRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository
    )
    {
        $this->gameRepository = $gameRepository;
    }

    public function getNewsForums(?Game $game = null) : ForumCollection
    {
        $games = $game
            ? $game->subTree()
            : $this->gameRepository->getAllPublished();

        return $games->newsForums();
    }
}
