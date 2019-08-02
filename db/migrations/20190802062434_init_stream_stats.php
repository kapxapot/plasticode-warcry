<?php

use Phinx\Migration\AbstractMigration;

class InitStreamStats extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('stream_stats');

        $table
        ->addColumn('stream_id', 'integer')
        ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
        ->addColumn('finished_at', 'timestamp', ['null' => true])
        ->addColumn('remote_game', 'string', ['limit' => 250, 'null' => true])
        ->addColumn('remote_viewers', 'integer')
        ->addColumn('remote_status', 'string', ['limit' => 500, 'null' => true])
        ->addForeignKey('stream_id', 'streams', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
        ->create();
    }
}
