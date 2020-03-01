<?php

namespace App\Repositories;

use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;

class LocationRepository implements LocationRepositoryInterface
{
    public function getByName($name) : ?Location
    {
        return Location::query()
            ->where('name', $name)
            ->one();
    }
}
