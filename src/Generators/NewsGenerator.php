<?php

namespace App\Generators;

use App\Models\News;
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
        
        if ($this->isJustPublished($item, $data)) {
            $this->notify($item);
        }
    }

    private function notify(array $item) : void
    {
        $id = $item[$this->idField];
        $news = News::get($id);

        $msg = $this->twitterService->buildMessage($news);
        $this->twitter->tweet($msg);
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $id = $item[$this->idField];
        $news = $this->newsAggregatorService->getNews($id);
        
        $item['url'] = $news->url();

        return $item;
    }
}
