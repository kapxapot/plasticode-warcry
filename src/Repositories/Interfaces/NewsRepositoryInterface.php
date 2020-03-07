<?php

namespace App\Repositories\Interfaces;

use App\Models\News;
use Plasticode\Repositories\Idiorm\Interfaces\FullPublishInterface;

interface NewsRepositoryInterface extends FullPublishInterface
{
    public function getProtected(int $id) : ?News;
}
