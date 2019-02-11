<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ArticleCategory extends DbModel
{
    // getters - one
    
    public static function getByName($name)
    {
        return self::getByField('name_en', $name);
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
