<?php

namespace App\Generators;

use App\Repositories\Interfaces\StreamRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class StreamsGenerator extends TaggableEntityGenerator
{
    use Publishable;

    private StreamRepositoryInterface $streamRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->streamRepository = $container->streamRepository;
    }

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

        $stream = $this->streamRepository->get($id);

        $item['page_url'] = $stream->pageUrl();

        return $item;
    }
}
