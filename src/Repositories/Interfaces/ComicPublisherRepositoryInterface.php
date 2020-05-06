<?php

namespace App\Repositories\Interfaces;

use App\Models\ComicPublisher;

interface ComicPublisherRepositoryInterface
{
    function get(?int $id) : ?ComicPublisher;
}
