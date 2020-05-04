<?php

namespace App\Repositories\Interfaces;

use App\Models\Video;

interface VideoRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    public function getProtected(?int $id) : ?Video;
}
