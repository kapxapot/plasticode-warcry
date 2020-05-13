<?php

namespace App\Generators;

use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Services\NewsAggregatorService;
use App\Services\TwitterService;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class NewsGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private NewsRepositoryInterface $newsRepository;
    private NewsAggregatorService $newsAggregatorService;
    private TwitterService $twitterService;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->newsRepository = $container->newsRepository;
        $this->newsAggregatorService = $container->newsAggregatorService;
        $this->twitterService = $container->twitterService;
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

    /**
     * Disabled currently.
     */
    private function notify(array $item) : void
    {
        $id = $item[$this->idField];

        $news = $this->newsRepository->get($id);

        $msg = $this->twitterService->buildMessage($news);
        //$this->twitter->tweet($msg);
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
