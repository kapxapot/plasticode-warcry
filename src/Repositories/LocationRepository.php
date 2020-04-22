<?php

namespace App\Repositories;

use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class LocationRepository extends IdiormRepository implements LocationRepositoryInterface
{
    protected string $entityClass = Location::class;

    public function getByName(string $name) : ?Location
    {
        return $this
            ->query()
            ->where('name', $name)
            ->one();
    }
}
