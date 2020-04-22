<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property string $nameRu
 * @property string $nameEn
 */
class ArticleCategory extends DbModel
{
    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name_ru' => $this->nameRu,
            'name_en' => $this->nameEn,
        ];
    }
}
