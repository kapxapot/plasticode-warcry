<?php

use Phinx\Migration\AbstractMigration;

class AddGameIdToMenus extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('menus');

        $table
            ->addColumn('game_id', 'integer')
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->save();
    }
}
