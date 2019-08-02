<?php

use Phinx\Migration\AbstractMigration;

class InitSkills extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('skills');

        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('name_ru', 'string', ['limit' => 50])
            ->addColumn('icon', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('active', 'boolean', ['default' => false])
            ->addColumn('alias', 'string', ['limit' => 50, 'null' => true])
            ->create();
    }
}
