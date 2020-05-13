<?php

namespace App\Repositories\Interfaces;

use App\Models\Video;

interface VideoRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    function get(?int $id) : ?Video;
    function getProtected(?int $id) : ?Video;
}
