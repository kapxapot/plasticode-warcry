<?php

namespace App\Collections;

use App\Models\MenuItem;
use Plasticode\Collections\MenuItemCollection as BaseMenuItemCollection;

class MenuItemCollection extends BaseMenuItemCollection
{
    protected string $class = MenuItem::class;
}
