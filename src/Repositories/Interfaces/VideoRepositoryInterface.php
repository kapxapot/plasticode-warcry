<?php

namespace App\Repositories\Interfaces;

use App\Models\Video;

interface VideoRepositoryInterface
{
    public function getProtected(int $id) : ?Video;
}
