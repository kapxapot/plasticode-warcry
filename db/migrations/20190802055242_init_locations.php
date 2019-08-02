<?php

use Phinx\Migration\AbstractMigration;

class InitLocations extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('locations');

        $table
            ->addColumn('name', 'string', ['limit' => 250])
            ->create();
    }
}
