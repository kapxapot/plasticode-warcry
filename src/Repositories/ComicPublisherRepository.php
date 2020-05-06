<?php

namespace App\Repositories;

use App\Models\ComicPublisher;
use App\Repositories\Interfaces\ComicPublisherRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ComicPublisherRepository extends IdiormRepository implements ComicPublisherRepositoryInterface
{
    protected string $entityClass = ComicPublisher::class;

    public function get(?int $id) : ?ComicPublisher
    {
        return $this->getEntity($id);
    }
}
