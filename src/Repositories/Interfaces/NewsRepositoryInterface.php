<?php

namespace App\Repositories\Interfaces;

use App\Models\News;

interface NewsRepositoryInterface
{
    function getProtected(?int $id) : ?News;
}
