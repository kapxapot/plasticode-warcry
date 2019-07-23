<?php

use Phinx\Migration\AbstractMigration;

class InitGalleryPictures extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('gallery_pictures');

        $table
            ->addColumn('author_id', 'integer')
            ->addColumn('game_id', 'integer', ['null' => true])
            ->addColumn('comment', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('official', 'boolean', ['default' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('points', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('picture_type', 'enum', ['values' => ['jpeg', 'png', 'gif'], 'default' => 'jpeg'])
            ->addColumn('thumb_type', 'enum', ['values' => ['jpeg', 'png', 'gif'], 'default' => 'jpeg'])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('width', 'integer', ['null' => true])
            ->addColumn('height', 'integer', ['null' => true])
            ->addColumn('avg_color', 'string', ['limit' => 50, 'null' => true])
            ->addForeignKey('author_id', 'gallery_authors', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
