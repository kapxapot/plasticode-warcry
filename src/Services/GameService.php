<?php

namespace App\Services;

use App\Config\Interfaces\GameConfigInterface;

class GameService
{
    /** @var GameConfigInterface */
    private $config;

    public function __construct(
        GameConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function isPriorityGame(string $gameName) : bool
    {
        $priorityGames = $this->config->streamPriorityGames();

        return in_array(mb_strtolower($gameName), $priorityGames);
    }
}
