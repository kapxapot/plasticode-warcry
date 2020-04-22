<?php

namespace App\Repositories;

use App\Models\News;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;

class NewsRepository extends IdiormRepository implements NewsRepositoryInterface
{
    use FullPublishedRepository;
    use ProtectedRepository;

    protected string $entityClass = News::class;

    public function getProtected(?int $id) : ?News
    {
        return $this->getProtectedEntity($id);
    }
}
