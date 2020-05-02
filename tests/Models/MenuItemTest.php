<?php

namespace App\Tests;

use App\Models\MenuItem;
use PHPUnit\Framework\TestCase;

final class MenuItemTest extends TestCase
{
    public function testMenuId() : void
    {
        $sectionId = 1;

        $item = new MenuItem(
            [
                'id' => 1,
                'section_id' => $sectionId,
                'position' => 1,
                'text' => 'Hahaha',
            ]
        );

        /** @var MenuItem */
        $item = $item->withUrl('url');

        $this->assertEquals($item->sectionId, $sectionId);
        $this->assertEquals($item->menuId(), $sectionId);
        $this->assertEquals($item->menuId, $sectionId);
    }
}
