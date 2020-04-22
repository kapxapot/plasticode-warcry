<?php

namespace App\Models;

use Plasticode\Models\MenuItem as MenuItemBase;

class MenuItem extends MenuItemBase
{
    protected string $parentIdField = 'section_id';
}
