<?php

use Phinx\Migration\AbstractMigration;

class InitForumcoreTags extends AbstractMigration
{
    public function change()
    {
        $table = $this->table(
            'forumcore_tags',
            ['id' => false, 'primary_key' => ['tag_id']]
        );

        $table
            ->addColumn('tag_id', 'integer')
            ->addColumn('tag_meta_app', 'string', ['limit' => 200])
            ->addColumn('tag_meta_area', 'string', ['limit' => 200])
            ->addColumn('tag_text', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('tag_meta_id', 'integer', ['default' => 0])
            ->create();
    }
}
