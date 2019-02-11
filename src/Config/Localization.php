<?php

namespace App\Config;

use Plasticode\Config\Localization as LocalizationBase;

class Localization extends LocalizationBase
{
    protected function ru()
    {
        return array_merge(
            parent::ru(),
            [
                'news_forum_id' => 'Id новостного форума',
                'main_forum_id' => 'Id игрового форума',
                'name_ru' => 'Заголовок',
                'name_en' => 'Английский заголовок',
                'comment' => 'Заголовок',
                'title' => 'Название',
                'stream_id' => 'Код',
            ]
        );
    }
}
