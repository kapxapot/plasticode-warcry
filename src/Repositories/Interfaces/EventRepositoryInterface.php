<?php

namespace App\Repositories\Interfaces;

use App\Models\Event;

interface EventRepositoryInterface
{
    function getProtected(?int $id) : ?Event;
}
