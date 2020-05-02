<?php

namespace App\Repositories;

use App\Collections\MenuItemCollection;
use App\Models\MenuItem;
use App\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Idiorm\MenuItemRepository as BaseMenuItemRepository;

class MenuItemRepository extends BaseMenuItemRepository implements MenuItemRepositoryInterface
{
    protected string $entityClass = MenuItem::class;

    protected string $parentIdField = 'section_id';

    public function get(?int $id) : ?MenuItem
    {
        return $this->getEntity($id);
    }

    public function getAllByMenuId(int $menuId) : MenuItemCollection
    {
        return MenuItemCollection::from(
            parent::getAllByMenuId($menuId)
        );
    }
}
