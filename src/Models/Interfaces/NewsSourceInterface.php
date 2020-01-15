<?php

namespace App\Models\Interfaces;

use App\Models\Game;
use Plasticode\Query;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\TaggableInterface;

interface NewsSourceInterface extends LinkableInterface, TaggableInterface
{
    public static function getNewsByTag(string $tag) : Query;
    public static function getLatestNews(Game $game = null, int $exceptNewsId = null) : Query;
    public static function getNewsByYear(int $year) : Query;

    public static function getNewsBefore(Game $game, string $date) : Query;
    public static function getNewsAfter(Game $game, string $date) : Query;

    public function game() : ?Game;
    public function largeImage() : ?string;
    public function image() : ?string;
    public function video() : ?string;

    public function displayTitle() : string;
    public function fullText() : ?string;
    public function shortText() : ?string;
}
