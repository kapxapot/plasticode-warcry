<?php

namespace App\Collections;

use App\Models\Location;
use Plasticode\TypedCollection;

class LocationCollection extends TypedCollection
{
    protected string $class = Location::class;
}
