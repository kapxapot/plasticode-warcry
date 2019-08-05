<?php

use Phinx\Migration\AbstractMigration;

class InitForumtopics extends AbstractMigration
{
    public function change()
    {
        $table = $this->table(
            'forumtopics',
            ['id' => false, 'primary_key' => ['tid']]
        );

        $table
            ->addColumn('tid', 'integer')
            ->addColumn('start_date', 'integer', ['null' => true])
            ->addColumn('forum_id', 'integer', ['default' => 0])
            ->addColumn('starter_id', 'integer', ['default' => 0])
            ->addColumn('starter_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('title', 'string', ['limit' => 250])
            ->create();
    }
}
