<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\LocationCollection;
use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LocationRepositoryMock implements LocationRepositoryInterface
{
    private LocationCollection $locations;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->locations = LocationCollection::make($seeder->seed());
    }

    public function getByName(string $name) : ?Location
    {
        return $this
            ->locations
            ->first('name', $name);
    }
}
