<?php

namespace App\Services;

use App\Config\Interfaces\GameConfigInterface;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;

class GameService
{
    private GameRepositoryInterface $gameRepository;
    private GameConfigInterface $config;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        GameConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function getDefault() : ?Game
    {
        $id = $this->config->defaultGameId();

        return $this->gameRepository->get($id);
    }

    public function isPriorityGame(string $gameName) : bool
    {
        $priorityGames = $this->config->streamPriorityGames();

        return in_array(mb_strtolower($gameName), $priorityGames);
    }

    public function getByTwitchName(string $name) : ?Game
    {
        return
            $this->gameRepository->getByName($name)
            ??
            $this->getDefault();
    }
}
