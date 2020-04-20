<?php

namespace App\Repositories;

use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;

class VideoRepository extends IdiormRepository implements VideoRepositoryInterface
{
    use FullPublishedRepository;
    use ProtectedRepository;

    protected $entityClass = Video::class;

    public function getProtected(?int $id) : ?Video
    {
        return $this->getProtectedEntity($id);
    }
}
