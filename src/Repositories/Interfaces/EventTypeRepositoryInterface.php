<?php

namespace App\Repositories\Interfaces;

use App\Models\EventType;

interface EventTypeRepositoryInterface
{
    function get(?int $id) : ?EventType;
}
