<?php

use Phinx\Migration\AbstractMigration;

class InitForumposts extends AbstractMigration
{
    public function change()
    {
        $table = $this->table(
            'forumposts',
            ['id' => false, 'primary_key' => ['pid']]
        );

        $table
            ->addColumn('pid', 'integer')
            ->addColumn('topic_id', 'integer', ['default' => 0])
            ->addColumn('new_topic', 'boolean', ['null' => true, 'default' => false])
            ->addColumn('post', 'text', ['null' => true])
            ->create();
    }
}
