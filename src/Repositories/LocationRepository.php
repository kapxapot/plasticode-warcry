<?php

namespace App\Repositories;

use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Repositories\Idiorm\IdiormRepository;

class LocationRepository extends IdiormRepository implements LocationRepositoryInterface
{
    public function getByName($name) : ?Location
    {
        return Location::query()
            ->where('name', $name)
            ->one();
    }
}
