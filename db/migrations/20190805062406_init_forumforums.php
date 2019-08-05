<?php

use Phinx\Migration\AbstractMigration;

class InitForumforums extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('forumforums');

        $table->create();

        $table = $this->table('games');

        $table
            ->addForeignKey('news_forum_id', 'forumforums', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('main_forum_id', 'forumforums', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->save();
    }
}
