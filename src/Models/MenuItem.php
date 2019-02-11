<?php

namespace App\Models;

use Plasticode\Models\MenuItem as MenuItemBase;

class MenuItem extends MenuItemBase
{
    protected static $parentIdField = 'section_id';
}
