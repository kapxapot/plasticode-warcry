<?php

namespace App\Repositories\Interfaces;

use App\Models\Location;

interface LocationRepositoryInterface
{
    function getByName(string $name) : ?Location;
}
