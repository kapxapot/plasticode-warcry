<?php

namespace App\Generators;

use App\Repositories\Interfaces\VideoRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class VideosGenerator extends TaggableEntityGenerator
{
    use Publishable;

    private VideoRepositoryInterface $videoRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->videoRepository = $container->videoRepository;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $video = $this->videoRepository->get($id);

        $item['url'] = $video->url();

        return $item;
    }
}
