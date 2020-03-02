<?php

namespace App\Tests\Seeders;

use App\Models\Location;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

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
