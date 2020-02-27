<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ArticleCategory extends DbModel
{
    public function serialize() : array
    {
        return [
            'id' => $this->id,
            'name_ru' => $this->nameRu,
            'name_en' => $this->nameEn,
        ];
    }
}
