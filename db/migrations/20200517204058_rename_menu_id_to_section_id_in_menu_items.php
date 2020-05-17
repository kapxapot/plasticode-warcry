<?php

use Phinx\Migration\AbstractMigration;

class RenameMenuIdToSectionIdInMenuItems extends AbstractMigration
{
    public function create()
    {
        $table = $this->table('menu_items');

        $table
            ->renameColumn('menu_id', 'section_id')
            ->save();
    }
}
