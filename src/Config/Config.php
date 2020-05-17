<?php

namespace App\Config;

use App\Config\Interfaces\GalleryConfigInterface;
use App\Config\Interfaces\GameConfigInterface;
use App\Config\Interfaces\RecipeConfigInterface;
use App\Config\Interfaces\StreamConfigInterface;
use Plasticode\Config\Config as ConfigBase;

class Config extends ConfigBase implements GalleryConfigInterface, GameConfigInterface, RecipeConfigInterface, StreamConfigInterface
{
    public function defaultWoWIcon() : string
    {
        return $this->get('default_wow_icon', 'inv_misc_questionmark');
    }

    public function defaultGameId() : ?int
    {
        return $this->get('default_game_id');
    }

    public function galleryPicsPerPage(): int
    {
        return $this->get('gallery.pics_per_page', 50);
    }

    /**
     * @return string[]
     */
    public function streamPriorityGames() : array
    {
        $games = $this->get('streams.priority_games', []);

        return array_map(
            fn (string $game) => mb_strtolower($game),
            $games
        );
    }

    public function streamTimeToLive() : int
    {
        return $this->get('streams.ttl', 14);
    }

    public function streamAnalysisPeriod() : int
    {
        return $this->get('streams.analysis_period', 30);
    }
}
