<?php

use Phinx\Migration\AbstractMigration;

class AddForumNameToUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');

        $table
            ->addColumn('forum_name', 'string', ['limit' => 100, 'null' => true])
            ->save();
    }
}
