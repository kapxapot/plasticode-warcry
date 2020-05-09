<?php

namespace App\Collections;

use App\Models\ComicStandalone;
use Plasticode\Collections\Basic\TaggedCollection;

class ComicStandaloneCollection extends TaggedCollection
{
    protected string $class = ComicStandalone::class;
}
