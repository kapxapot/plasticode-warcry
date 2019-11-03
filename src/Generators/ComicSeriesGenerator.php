<?php

namespace App\Generators;

use App\Models\ComicSeries;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;

class ComicSeriesGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $series = ComicSeries::get($item[$this->idField]);
        
        $item['page_url'] = $series->pageUrl();

        return $item;
    }
}
