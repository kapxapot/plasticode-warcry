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
        $this->gameRepository = $gameRepository;

        $this->config = $config;
    }

    public function isPriorityGame(string $gameName) : bool
    {
        $priorityGames = $this->config->streamPriorityGames();

        return in_array(
            mb_strtolower($gameName),
            $priorityGames
        );
    }

    public function resultIcon(Game $game) : ?string
    {
        return $game->icon
            ??
            ($game->parent()
                ? $game->parent()->resultIcon()
                : null)
            ??
            ($this->gameRepository->getDefault()
                ? $this->gameRepository->getDefault()->resultIcon()
                : null);
    }

    public function resultAlias(Game $game) : ?string
    {
        return $game->alias
            ??
            ($game->parent()
                ? $game->parent()->resultAlias()
                : null)
            ??
            ($this->gameRepository->getDefault()
                ? $this->gameRepository->getDefault()->resultAlias()
                : null);
    }
}
