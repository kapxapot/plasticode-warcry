<?php

namespace App\Testing\Mocks\Repositories;

use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LocationRepositoryMock implements LocationRepositoryInterface
{
    /** @var Collection */
    private $locations;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->locations = Collection::make($seeder->seed());
    }

    public function getByName($name) : ?Location
    {
        return $this
            ->locations
            ->where('name', $name)
            ->first();
    }
}
