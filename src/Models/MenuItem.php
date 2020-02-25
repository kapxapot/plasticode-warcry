<?php

namespace App\Models;

use Plasticode\Models\MenuItem as MenuItemBase;

class MenuItem extends MenuItemBase
{
    protected const ParentIdField = 'section_id';
}
