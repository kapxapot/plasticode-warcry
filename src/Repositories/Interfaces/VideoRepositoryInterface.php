<?php

namespace App\Repositories\Interfaces;

use App\Models\Video;

interface VideoRepositoryInterface extends NewsSourceRepositoryInterface
{
    public function getProtected(?int $id) : ?Video;
}
