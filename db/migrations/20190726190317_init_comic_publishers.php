<?php

use Phinx\Migration\AbstractMigration;

class InitComicPublishers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('comic_publishers');

        $table
            ->addColumn('name', 'string', ['limit' => 250])
            ->addColumn('website', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
