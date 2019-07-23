<?php

use Phinx\Migration\AbstractMigration;

class InitStreams extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('streams');

        $table
            ->addColumn('stream_id', 'string', ['limit' => 100])
            ->addColumn('stream_alias', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('remote_title', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('remote_online', 'boolean', ['default' => false])
            ->addColumn('remote_updated_at', 'timestamp', ['null' => true])
            ->addColumn('remote_viewers', 'integer')
            ->addColumn('remote_game', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('remote_status', 'string', ['limit' => 1000, 'null' => true])
            ->addColumn('remote_logo', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('remote_online_at', 'timestamp', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('priority', 'boolean', ['default' => false])
            ->addColumn('gender_id', 'integer', ['null' => true, 'default' => 1])
            ->addColumn('channel', 'boolean', ['default' => false])
            ->addColumn('official', 'boolean', ['default' => false])
            ->addColumn('official_ru', 'boolean', ['default' => false])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('gender_id', 'genders', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
