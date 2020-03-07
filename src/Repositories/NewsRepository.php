<?php

namespace App\Repositories;

use App\Models\News;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\ProtectedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublish;

class NewsRepository extends ProtectedRepository implements NewsRepositoryInterface
{
    use FullPublish;

    protected $entityClass = News::class;

    public function getProtected(int $id) : ?News
    {
        return $this->getProtectedEntity($id);
    }
}
