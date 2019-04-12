<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ArticleCategory extends DbModel
{
    // getters - one
    
    public static function getByName($name)
    {
        return self::query()
            ->where('name_en', $name)
            ->one();
    }
    
    // funcs
    
    public function serialize()
    {
        return [
            'id' => $this->id,
            'name_ru' => $this->nameRu,
            'name_en' => $this->nameEn,
        ];
    }
}
