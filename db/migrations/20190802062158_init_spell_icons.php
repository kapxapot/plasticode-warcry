<?php

use Phinx\Migration\AbstractMigration;

class InitSpellIcons extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('spell_icons');

        $table
            ->addColumn('icon', 'string', ['limit' => 100])
            ->create();
    }
}
