<?php

namespace App\Models\Interfaces;

use Plasticode\Query;
use Plasticode\Models\Interfaces\LinkableInterface;

interface NewsSourceInterface extends LinkableInterface
{
    public static function getNewsByTag($tag) : Query;
    public static function getLatestNews($game, $exceptNewsId) : Query;
    public static function getNewsByYear($year) : Query;
    
    public static function getNewsBefore($game, $date) : Query;
    public static function getNewsAfter($game, $date) : Query;
    
    public function displayTitle();
    public function fullText();
    public function shortText();
}
