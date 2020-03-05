<?php

namespace App\Testing\Seeders;

use App\Models\Article;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;
use Plasticode\Util\Date;

class ArticleSeeder implements ArraySeederInterface
{
    /**
     * @return Article[]
     */
    public function seed() : array
    {
        return [
            new Article(
                [
                    'id' => 1,
                    'name_ru' => 'О сайте',
                    'name_en' => 'About Us',
                    'text' => 'We are awesome. Work with us.',
                    'published' => 0,
                ]
            ),
            new Article(
                [
                    'id' => 2,
                    'name_ru' => 'Иллидан Ярость Бури',
                    'name_en' => 'Illidan Stormrage',
                    'cat' => 1,
                    'text' => 'Illidan is a bad boy. Once a night elf, now a demon. Booo.',
                    'published' => 1,
                    'published_at' => Date::dbNow(),
                    'aliases' => 'Illidan',
                ]
            ),
        ];
    }
}
