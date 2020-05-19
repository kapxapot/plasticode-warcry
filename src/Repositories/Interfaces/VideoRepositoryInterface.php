<?php

namespace App\Repositories\Interfaces;

use App\Collections\VideoCollection;
use App\Models\Video;

interface VideoRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    function get(?int $id) : ?Video;
    function getProtected(?int $id) : ?Video;
    function getAllPublished() : VideoCollection;
}
