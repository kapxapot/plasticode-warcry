<?php

namespace App\Repositories\Interfaces;

use App\Models\News;

interface NewsRepositoryInterface
{
    public function getProtected(int $id) : ?News;
}
