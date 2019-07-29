<?php

namespace App\Config;

class Captcha
{
    public function getReplaces() : array
    {
        return [
            'ысяч' => [ 'ыщич', 'ыщ', 'ишч', 'исеч' ],
            'дцат' => [ 'цодд', 'цыыд', 'цадз' ],
            'десят' => [ 'дисяд', 'дзисят' ],
            'сот' => [ 'соод', 'цот', 'цод' ],
            'один' => [ 'адзин', 'адин', 'адын' ],
            'одн' => [ 'адн', 'адын' ],
            'дв' => [ 'дыв', 'дэв' ],
            'три' => [ 'тыри', 'тари' ],
            'четыре' => [ 'чатыри', 'читыри', 'чтыря' ],
            'пять' => [ 'пият', 'пиадь' ],
            'шест' => [ 'шээз', 'щэсс' ],
            'ст' => [ 'сат', 'зт' ],
            'восемь' => [ 'восим', 'воссям' ],
            'семь' => [ 'сеем', 'сёмь' ],
            'девя' => [ 'дэви', 'дзювя' ],
            'сорок' => [ 'сорык', 'сораг' ],
            'лион' => [ 'ляон', 'леонн' ],
            'милли' => [ 'мюлле', 'мялле' ],
            'ард' => [ 'ярд', 'йард' ],
        ];
    }
}
