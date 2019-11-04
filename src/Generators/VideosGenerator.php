<?php

namespace App\Generators;

use App\Models\Video;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;

class VideosGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $id = $item[$this->idField];
        $video = Video::get($id);
        
        $item['url'] = $video->url();

        return $item;
    }
}
