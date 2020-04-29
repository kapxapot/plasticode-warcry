<?php

use Phinx\Migration\AbstractMigration;

class AddParentIdToForumforums extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('forumforums');

        // no foreign key here
        // because it won't work for -1 default value

        $table
            ->addColumn('parent_id', 'integer', ['null' => true, 'default' => -1])
            ->save();
    }
}
