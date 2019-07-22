<?php

use Phinx\Migration\AbstractMigration;

class InitNews extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('news');

        $table
            ->addColumn('title', 'string', ['limit' => 250])
            ->addColumn('game_id', 'integer')
            ->addColumn('text', 'text')
            ->addColumn('cache', 'text', ['null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
