<?php

namespace App\Hydrators;

use App\Models\Region;
use App\Repositories\Interfaces\RegionRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class RegionHydrator extends Hydrator
{
    private RegionRepositoryInterface $regionRepository;

    public function __construct(
        RegionRepositoryInterface $regionRepository
    )
    {
        $this->regionRepository = $regionRepository;
    }

    /**
     * @param Region $entity
     */
    public function hydrate(DbModel $entity) : Region
    {
        return $entity
            ->withParent(
                fn () => $this->regionRepository->get($entity->parentId)
            );
    }
}
