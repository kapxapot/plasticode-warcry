<?php

namespace App\Testing\Seeders;

use App\Models\ArticleCategory;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

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
