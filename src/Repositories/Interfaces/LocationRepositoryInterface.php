<?php

namespace App\Repositories\Interfaces;

use App\Models\Location;

interface LocationRepositoryInterface
{
    public function getByName($name) : ?Location;
}
