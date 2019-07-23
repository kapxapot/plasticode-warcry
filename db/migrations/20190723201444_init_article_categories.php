<?php

use Phinx\Migration\AbstractMigration;

class InitArticleCategories extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('article_categories');

        $table
            ->addColumn('name_ru', 'string', ['limit' => 50])
            ->addColumn('name_en', 'string', ['limit' => 50])
            ->create();

        $table = $this->table('articles');

        $table
            ->addForeignKey('cat', 'article_categories', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->save();
    }
}
