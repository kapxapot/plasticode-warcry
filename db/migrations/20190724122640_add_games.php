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
                    'name' => 'Warcraft',
                    'alias' => 'warcraft',
                    'published' => 1,
                    'position' => 2,
                    'icon' => 'https://warcry.ru/images/icons/games/large/wow.png',
                    'autotags' => 'Warcraft',
                ],
                [
                    'id' => 3,
                    'name' => 'Diablo',
                    'alias' => 'diablo',
                    'published' => 1,
                    'position' => 3,
                    'icon' => 'https://warcry.ru/images/icons/games/small/diablo3.png',
                    'autotags' => 'Diablo',
                ],
                [
                    'id' => 5,
                    'name' => 'Warcry.ru',
                    'alias' => 'warcryru',
                    'published' => 1,
                    'position' => 1,
                    'icon' => 'https://warcry.ru/images/icons/games/medium/wcru.png',
                    'autotags' => 'Warcry.ru',
                ],
                [
                    'id' => 10,
                    'name' => 'World of Warcraft',
                    'position' => 201,
                    'autotags' => 'WoW, World of Warcraft',
                    'twitch_name' => 'WoW',
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->getQueryBuilder()
            ->delete('games')
            ->where(['id' => 1])
            ->execute();

        $this->getQueryBuilder()
            ->delete('games')
            ->where(['id' => 3])
            ->execute();

        $this->getQueryBuilder()
            ->delete('games')
            ->where(['id' => 5])
            ->execute();

        $this->getQueryBuilder()
            ->delete('games')
            ->where(['id' => 10])
            ->execute();
    }
}
