<?php

namespace App\Repositories;

use App\Models\Region;
use App\Repositories\Interfaces\RegionRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class RegionRepository extends IdiormRepository implements RegionRepositoryInterface
{
    protected string $entityClass = Region::class;

    public function get(?int $id) : ?Region
    {
        return $this->getEntity($id);
    }
}
