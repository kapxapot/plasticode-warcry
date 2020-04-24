<?php

namespace App\Repositories\Interfaces;

use App\Models\News;

interface NewsRepositoryInterface extends NewsSourceRepositoryInterface
{
    function getProtected(?int $id) : ?News;
}
