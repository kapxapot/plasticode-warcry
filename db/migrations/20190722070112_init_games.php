<?php

use Phinx\Migration\AbstractMigration;

class InitGames extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('games');

        $table
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('alias', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('news_forum_id', 'integer', ['null' => true])
            ->addColumn('main_forum_id', 'integer', ['null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('position', 'integer', ['null' => true])
            ->addColumn('icon', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('parent_id', 'integer', ['null' => true])
            ->addColumn('autotags', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('twitch_name', 'string', ['limit' => 100, 'null' => true])
            ->addForeignKey('parent_id', 'games', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
