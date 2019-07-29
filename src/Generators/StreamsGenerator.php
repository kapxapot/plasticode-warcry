<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

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
    
    public function beforeSave(array $data, $id = null) : array
    {
        $data = parent::beforeSave($data, $id);

        $data = $this->publishIfNeeded($data);

        return $data;
    }
}
