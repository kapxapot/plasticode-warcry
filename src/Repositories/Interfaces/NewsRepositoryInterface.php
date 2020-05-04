<?php

namespace App\Repositories\Interfaces;

use App\Models\News;

interface NewsRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    function getProtected(?int $id) : ?News;
}
