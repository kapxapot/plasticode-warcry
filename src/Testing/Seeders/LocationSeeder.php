<?php

namespace App\Testing\Seeders;

use App\Models\Location;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class LocationSeeder implements ArraySeederInterface
{
    /**
     * @return Location[]
     */
    public function seed() : array
    {
        return [
            new Location(
                [
                    'id' => 1,
                    'name' => 'Zangarmarsh',
                ]
            )
        ];
    }
}
