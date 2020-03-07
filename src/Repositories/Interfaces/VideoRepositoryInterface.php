<?php

namespace App\Repositories\Interfaces;

use App\Models\Video;
use Plasticode\Repositories\Idiorm\Interfaces\FullPublishInterface;

interface VideoRepositoryInterface extends FullPublishInterface
{
    public function getProtected(int $id) : ?Video;
}
