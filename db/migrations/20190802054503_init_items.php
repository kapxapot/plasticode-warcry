<?php

use Phinx\Migration\AbstractMigration;

class InitItems extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('items');

        $table
            ->addColumn('name', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('name_ru', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('quality', 'integer', ['null' => true])
            ->addColumn('sellprice', 'integer', ['null' => true])
            ->addColumn('avgbuyout', 'integer', ['null' => true])
            ->addColumn('buyprice', 'integer', ['null' => true])
            ->addColumn('icon', 'string', ['limit' => 100])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
