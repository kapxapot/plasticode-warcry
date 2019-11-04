<?php

namespace App\Generators;

use App\Models\Stream;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;

class StreamsGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['title'] = $this->rule('text')->streamTitleAvailable($id);
        $rules['stream_id'] = $this->rule('extendedAlias')->streamIdAvailable($id);
        
        return $rules;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $id = $item[$this->idField];
        $stream = Stream::get($id);
        
        $item['page_url'] = $stream->pageUrl();

        return $item;
    }
}
