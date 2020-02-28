<?php

namespace App\Tests\Seeders;

use App\Models\ArticleCategory;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

class ArticleCategorySeeder implements ArraySeederInterface
{
    /**
     * @return ArticleCategory[]
     */
    public function seed() : array
    {
        return [
            new ArticleCategory(
                [
                    'id' => 1,
                    'name_ru' => 'Персонаж',
                    'name_en' => 'Character',
                ]
            )
        ];
    }
}
