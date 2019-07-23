<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

class EventsGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function beforeSave($data, $id = null)
    {
        $data = parent::beforeSave($data, $id);

        $data['cache'] = null;

        $data = $this->publishIfNeeded($data);

        return $data;
    }
}
