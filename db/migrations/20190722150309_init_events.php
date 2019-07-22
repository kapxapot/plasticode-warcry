<?php

use Phinx\Migration\AbstractMigration;

class InitEvents extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('events');

        $table
            ->addColumn('name', 'string', ['limit' => 250])
            ->addColumn('type_id', 'integer')
            ->addColumn('starts_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('ends_at', 'timestamp', ['null' => true])
            ->addColumn('website', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('region_id', 'integer', ['null' => true])
            ->addColumn('game_id', 'integer', ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('announce', 'boolean', ['default' => false])
            ->addColumn('cache', 'text', ['null' => true])
            ->addColumn('unknown_end', 'boolean', ['default' => false])
            ->addColumn('address', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('important', 'boolean', ['default' => false])
            ->addForeignKey('type_id', 'event_types', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('region_id', 'regions', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
