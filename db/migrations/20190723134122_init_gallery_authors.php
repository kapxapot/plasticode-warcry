<?php

use Phinx\Migration\AbstractMigration;

class InitGalleryAuthors extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('gallery_authors');

        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('category_id', 'integer')
            ->addColumn('deviant', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('art_station', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('alias', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('real_name', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('real_name_en', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addForeignKey('category_id', 'gallery_author_categories', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
