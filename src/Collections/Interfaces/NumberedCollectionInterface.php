<?php

namespace App\Collections\Interfaces;

use App\Models\Interfaces\NumberedInterface;

interface NumberedCollectionInterface
{
    function byNumber(int $number) : ?NumberedInterface;
    function prevBy(int $number) : ?NumberedInterface;
    function nextBy(int $number) : ?NumberedInterface;
    function maxNumber() : int;
}
