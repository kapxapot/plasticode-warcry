<?php

namespace App\Generators;

use App\Models\Event;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;

class EventsGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);

        $data['cache'] = null;

        return $data;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $id = $item[$this->idField];
        $event = Event::get($id);
        
        $item['url'] = $event->url();

        return $item;
    }
}
