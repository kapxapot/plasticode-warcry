<?php

namespace App\Repositories\Interfaces;

use App\Models\Event;
use Plasticode\Repositories\Idiorm\Interfaces\FullPublishInterface;

interface EventRepositoryInterface extends FullPublishInterface
{
    public function getProtected(int $id) : ?Event;
}
