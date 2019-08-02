<?php

use Phinx\Migration\AbstractMigration;

class InitRecipeSources extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('recipe_sources');

        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('name_ru', 'string', ['limit' => 50])
            ->create();
    }
}
