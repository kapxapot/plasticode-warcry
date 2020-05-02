<?php

namespace App\Collections;

use App\Models\Menu;
use Plasticode\Collections\MenuCollection as BaseMenuCollection;

class MenuCollection extends BaseMenuCollection
{
    protected string $class = Menu::class;
}
