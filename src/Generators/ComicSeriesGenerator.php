<?php

namespace App\Generators;

use App\Repositories\Interfaces\ComicSeriesRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class ComicSeriesGenerator extends TaggableEntityGenerator
{
    use Publishable;

    private ComicSeriesRepositoryInterface $comicSeriesRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->comicSeriesRepository = $container->comicSeriesRepository;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $series = $this->comicSeriesRepository->get($id);

        $item['page_url'] = $series->pageUrl();

        return $item;
    }
}
