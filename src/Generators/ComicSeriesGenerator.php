<?php

namespace App\Generators;

use App\Models\ComicSeries;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

class ComicSeriesGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function beforeSave($data, $id = null)
    {
        $data = parent::beforeSave($data, $id);
        
        $data = $this->publishIfNeeded($data);

        return $data;
    }

    public function afterLoad($item)
    {
        $item = parent::afterLoad($item);
        
        $series = ComicSeries::get($item['id']);
        
        $item['page_url'] = $series->pageUrl();

        return $item;
    }
}
