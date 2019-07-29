<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

class NewsGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function beforeSave(array $data, $id = null) : array
    {
        $data = parent::beforeSave($data, $id);
        
        $data['cache'] = null;

        $data = $this->publishIfNeeded($data);

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
            $url = $this->linker->news($item->id);
            $url = $this->linker->abs($url);
            
            /*$this->telegram->sendMessage('warcry', "Опубликована новость:
<a href=\"{$url}\">{$item->title}</a>");*/
        }
    }
}
