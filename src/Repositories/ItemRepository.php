<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\Interfaces\ItemRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ItemRepository extends IdiormRepository implements ItemRepositoryInterface
{
    protected string $entityClass = Item::class;

    public function get(?int $id) : ?Item
    {
        return $this->getEntity($id);
    }

    public function create(array $data) : Item
    {
        return $this->createEntity($data);
    }

    public function save(Item $item) : Item
    {
        return $this->saveEntity($item);
    }
}
