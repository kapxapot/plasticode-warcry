<?php

namespace App\Models;

use Plasticode\Models\MenuItem as MenuItemBase;

/**
 * @property integer $sectionId
 */
class MenuItem extends MenuItemBase
{
    /**
     * Override for the base class' menuId property.
     */
    public function menuId() : int
    {
        return $this->sectionId;
    }
}
