<?php

use Phinx\Migration\AbstractMigration;

class InitComicIssues extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('comic_issues');

        $table
            ->addColumn('series_id', 'integer')
            ->addColumn('number', 'integer')
            ->addColumn('name_ru', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('name_en', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('issued_on', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('origin', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addForeignKey('series_id', 'comic_series', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
