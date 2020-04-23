<?php

namespace App\Models\Interfaces;

use App\Models\Game;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\TaggedInterface;

interface NewsSourceInterface extends LinkableInterface, TaggedInterface
{
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
