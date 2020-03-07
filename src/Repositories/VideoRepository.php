<?php

namespace App\Repositories;

use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\ProtectedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublish;

class VideoRepository extends ProtectedRepository implements VideoRepositoryInterface
{
    use FullPublish;

    protected $entityClass = Video::class;

    public function getProtected(int $id) : ?Video
    {
        return $this->getProtectedEntity($id);
    }
}
