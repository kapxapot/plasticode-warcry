<?php

namespace App\Config;

use App\Config\Interfaces\GameConfigInterface;
use App\Config\Interfaces\RecipeConfigInterface;
use App\Config\Interfaces\SkillConfigInterface;
use App\Config\Interfaces\StreamConfigInterface;
use Plasticode\Config\Config as ConfigBase;

class Config extends ConfigBase implements GameConfigInterface, RecipeConfigInterface, SkillConfigInterface, StreamConfigInterface
{
    public function defaultWoWIcon() : string
    {
        return $this->get('default_wow_icon', 'inv_misc_questionmark');
    }

    public function defaultGameId() : ?int
    {
        return $this->get('default_game_id');
    }

    /**
     * @return string[]
     */
    public function streamPriorityGames() : array
    {
        $games = $this->get('streams.priority_games', []);

        return array_map(
            function (string $game) {
                return mb_strtolower($game);
            },
            $games
        );
    }

    public function streamTimeToLive() : int
    {
        return $this->get('streams.ttl', 14);
    }
}
