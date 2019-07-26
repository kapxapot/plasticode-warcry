<?php

use Phinx\Migration\AbstractMigration;

class InitComicPages extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('comic_pages');

        $table
            ->addColumn('comic_issue_id', 'integer', ['null' => true])
            ->addColumn('comic_standalone_id', 'integer', ['null' => true])
            ->addColumn('number', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('pic_type', 'enum', ['values' => ['jpeg', 'png', 'gif'], 'default' => 'jpeg'])
            ->addColumn('width', 'integer', ['null' => true])
            ->addColumn('height', 'integer', ['null' => true])
            ->addForeignKey('comic_issue_id', 'comic_issues', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('comic_standalone_id', 'comic_standalones', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
