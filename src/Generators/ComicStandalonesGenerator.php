<?php

namespace App\Generators;

use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class ComicStandalonesGenerator extends TaggableEntityGenerator
{
    use Publishable;

    private ComicStandaloneRepositoryInterface $comicStandaloneRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->comicStandaloneRepository = $container->comicStandaloneRepository;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['admin_template'] = 'entity_with_upload';
        $options['admin_args'] = [
            'upload_path' => 'admin.comics.upload',
        ];

        return $options;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $comic = $this->comicStandaloneRepository->get($id);

        $item['page_url'] = $comic->pageUrl();
        $item['context_field'] = 'comic_standalone_id';

        return $item;
    }
}
