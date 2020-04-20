<?php

namespace App\Repositories\Interfaces;

use App\Models\Region;

interface RegionRepositoryInterface
{
    function get(?int $id) : ?Region;
}
