<?php

use Phinx\Migration\AbstractMigration;

class InitArticles extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('articles');

        $table
            ->addColumn('parent_id', 'integer', ['null' => true])
            ->addColumn('cat', 'integer', ['null' => true])
            ->addColumn('name_ru', 'string', ['limit' => 250])
            ->addColumn('name_en', 'string', ['limit' => 250])
            ->addColumn('hideeng', 'boolean', ['default' => false])
            ->addColumn('origin', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('cache', 'text', ['null' => true])
            ->addColumn('announce', 'boolean', ['default' => false])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('game_id', 'integer')
            ->addColumn('no_breadcrumb', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('aliases', 'string', ['limit' => 500, 'null' => true])
            ->addForeignKey('parent_id', 'articles', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
