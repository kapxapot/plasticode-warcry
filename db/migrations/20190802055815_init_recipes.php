<?php

use Phinx\Migration\AbstractMigration;

class InitRecipes extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('recipes');

        $table
            ->addColumn('level', 'integer')
            ->addColumn('cat', 'integer')
            ->addColumn('learnedat', 'integer')
            ->addColumn('school', 'integer')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('name_ru', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('source', 'string', ['limit' => 10])
            ->addColumn('rank', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('quality', 'string', ['limit' => 1])
            ->addColumn('skill_id', 'integer')
            ->addColumn('lvl_orange', 'integer', ['null' => true])
            ->addColumn('lvl_yellow', 'integer', ['null' => true])
            ->addColumn('lvl_green', 'integer', ['null' => true])
            ->addColumn('lvl_gray', 'integer', ['null' => true])
            ->addColumn('reagents', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('creates_min', 'integer', ['null' => true, 'default' => 1])
            ->addColumn('creates_max', 'integer', ['null' => true, 'default' => 1])
            ->addColumn('creates_id', 'integer', ['null' => true])
            ->addColumn('races', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('icon_cache', 'text', ['null' => true])
            ->addColumn('reagent_cache', 'text', ['null' => true])
            ->addColumn('specialization', 'integer', ['null' => true])
            ->addColumn('reqrace', 'integer', ['null' => true])
            ->addColumn('scaling', 'integer', ['null' => true])
            ->addColumn('nskillup', 'integer', ['null' => true])
            ->addColumn('trainingcost', 'integer', ['null' => true])
            ->addForeignKey('skill_id', 'skills', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
