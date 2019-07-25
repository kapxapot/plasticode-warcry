<?php

use Phinx\Migration\AbstractMigration;

class InitForumMembers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('forummembers', ['id' => false, 'primary_key' => ['member_id']]);

        $table
            ->addColumn('member_id', 'integer')
            ->addColumn('name', 'string', ['limit' => 255])
            ->create();
    }
}
