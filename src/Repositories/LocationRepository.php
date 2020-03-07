<?php

namespace App\Repositories;

use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class LocationRepository extends IdiormRepository implements LocationRepositoryInterface
{
    protected $entityClass = Location::class;

    public function getByName($name) : ?Location
    {
        return $this
            ->query()
            ->where('name', $name)
            ->one();
    }
}
