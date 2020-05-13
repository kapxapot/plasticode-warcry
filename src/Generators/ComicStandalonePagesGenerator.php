<?php

namespace App\Generators;

use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Psr\Container\ContainerInterface;

class ComicStandalonePagesGenerator extends ComicPagesGenerator
{
    private ComicStandaloneRepositoryInterface $comicStandaloneRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->comicStandaloneRepository = $container->comicStandaloneRepository;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['uri'] = 'comic_standalones/{id:\d+}/pages';
        $options['filter'] = 'comic_standalone_id';

        return $options;
    }

    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $comicId = $args['id'];

        $comic = $this->comicStandaloneRepository->get($comicId);

        $params['source'] = 'comic_standalones/' . $comicId . '/pages';

        $params['breadcrumbs'] = [
            [
                'text' => 'Комиксы',
                'link' => $this->router->pathFor('admin.entities.comic_standalones')
            ],
            ['text' => $comic->game()->name],
            ['text' => $comic->nameRu],
            ['text' => 'Страницы'],
        ];

        $params['hidden'] = [
            'comic_standalone_id' => $comicId,
        ];

        return $params;
    }
}
