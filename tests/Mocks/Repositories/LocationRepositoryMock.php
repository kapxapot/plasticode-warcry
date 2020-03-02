<?php

namespace App\Tests\Mocks\Repositories;

use App\Models\Location;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

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
