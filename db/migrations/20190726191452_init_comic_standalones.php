<?php

use Phinx\Migration\AbstractMigration;

class InitComicStandalones extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('comic_standalones');

        $table
            ->addColumn('game_id', 'integer')
            ->addColumn('name_ru', 'string', ['limit' => 250])
            ->addColumn('name_en', 'string', ['limit' => 250])
            ->addColumn('alias', 'string', ['limit' => 100])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('publisher_id', 'integer')
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('issued_on', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('origin', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('publisher_id', 'comic_publishers', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
