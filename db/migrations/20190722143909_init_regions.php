<?php

use Phinx\Migration\AbstractMigration;

class InitRegions extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('regions');

        $table
            ->addColumn('name_ru', 'string', ['limit' => 250])
            ->addColumn('name_en', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('parent_id', 'integer', ['null' => true])
            ->addColumn('terminal', 'boolean', ['default' => false])
            ->addForeignKey('parent_id', 'regions', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
