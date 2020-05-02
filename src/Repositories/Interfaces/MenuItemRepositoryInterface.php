<?php

namespace App\Repositories\Interfaces;

use App\Collections\MenuItemCollection;
use App\Models\MenuItem;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface as BaseMenuItemRepositoryInterface;

interface MenuItemRepositoryInterface extends BaseMenuItemRepositoryInterface
{
    function get(?int $id) : ?MenuItem;
    function getAllByMenuId(int $menuId) : MenuItemCollection;
}
