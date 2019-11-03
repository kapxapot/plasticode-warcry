<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;

class NewsGenerator extends TaggableEntityGenerator
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

    public function afterSave(array $item, array $data) : void
    {
        parent::afterSave($item, $data);
        
        $this->notify($item, $data);
    }

    private function notify(array $item, array $data) : void
    {
        if ($this->isJustPublished($item, $data)) {
            $url = $this->linker->news($item[$this->idField]);
            $url = $this->linker->abs($url);
            
            // $this->telegram->sendMessage(
            //     'warcry',
            //     '<a href="' . $url . '">' . $item['title'] . '</a>'
            // );
        }
    }
}
