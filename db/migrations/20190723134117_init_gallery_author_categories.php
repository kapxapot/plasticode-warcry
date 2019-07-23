<?php

use Phinx\Migration\AbstractMigration;

class InitGalleryAuthorCategories extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('gallery_author_categories');

        $table
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('alias', 'string', ['limit' => 100])
            ->addColumn('position', 'integer')
            ->create();

        $table
            ->insert([
                [
                    'id' => 1,
                    'name' => 'Blizzard',
                    'alias' => 'blizzard',
                    'position' => 2,
                ],
                [
                    'id' => 2,
                    'name' => 'Профессионалы',
                    'alias' => 'professional',
                    'position' => 1,
                ],
                [
                    'id' => 3,
                    'name' => 'Любители',
                    'alias' => 'fanart',
                    'position' => 3,
                ],
            ])
            ->save();
    }
}
