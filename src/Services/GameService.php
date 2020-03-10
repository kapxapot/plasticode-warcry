<?php

namespace App\Services;

use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;

class GameService
{
    /** @var GameRepositoryInterface */
    private $gameRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository
    )
    {
        $this->gameRepository = $gameRepository;
    }

    public function isPriorityGame(string $gameName) : bool
    {
        $priorityGames = $this->config->streamPriorityGames();

        return in_array(mb_strtolower($gameName), $priorityGames);
    }
}
