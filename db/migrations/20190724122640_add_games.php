<?php

use Phinx\Migration\AbstractMigration;

class AddGames extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('games');

        $table
            ->insert([
                [
                    'id' => 1,
                    'name' => 'Администратор',
                    'tag' => 'admin',
                ],
                [
                    'id' => 2,
                    'name' => 'Редактор',
                    'tag' => 'editor',
                ],
                [
                    'id' => 3,
                    'name' => 'Автор',
                    'tag' => 'author',
                ],
            ])
            ->save();

            1	Warcraft	warcraft	1	2	/images/icons/games/large/wow.png	NULL	Warcraft	NULL
            3	Diablo	diablo	1	3	/images/icons/games/small/diablo3.png	NULL	Diablo	NULL
            5	Warcry.ru	warcryru	1	1	/images/icons/games/medium/wcru.png	NULL	Warcry.ru	NULL
            10	World of Warcraft	NULL	0	201	NULL	1	WoW, World of Warcraft	WoW

    }

    public function down()
    {
        $this->getQueryBuilder()
            ->delete('menus')
            ->where(['id' => 2])
            ->execute();

        $this->getQueryBuilder()
            ->delete('menus')
            ->where(['id' => 1])
            ->execute();
    }
}
