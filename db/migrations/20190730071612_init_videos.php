<?php

use Phinx\Migration\AbstractMigration;

class InitVideos extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('videos');

        $table
            ->addColumn('name', 'string', ['limit' => 250])
            ->addColumn('game_id', 'integer', ['null' => true])
            ->addColumn('youtube_code', 'string', ['limit' => 50])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('announce', 'boolean', ['default' => false])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
