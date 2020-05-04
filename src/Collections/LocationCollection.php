<?php

namespace App\Collections;

use App\Models\Location;
use Plasticode\Collections\Basic\DbModelCollection;

class LocationCollection extends DbModelCollection
{
    protected string $class = Location::class;
}
