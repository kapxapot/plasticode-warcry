<?php

namespace App\Models\Interfaces;

use App\Models\Game;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\TagsInterface;

/**
 * @property string $publishedAt
 */
interface NewsSourceInterface extends LinkableInterface, TagsInterface
{
    // static function getNewsByTag(string $tag) : Query;
    // static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query;
    // static function getNewsByYear(int $year) : Query;

    // static function getNewsBefore(Game $game, string $date) : Query;
    // static function getNewsAfter(Game $game, string $date) : Query;

    function game() : ?Game;
    function rootGame() : ?Game;
    function largeImage() : ?string;
    function image() : ?string;
    function video() : ?string;

    function displayTitle() : string;
    function fullText() : ?string;
    function shortText() : ?string;

    function publishedAtIso() : string;
}
