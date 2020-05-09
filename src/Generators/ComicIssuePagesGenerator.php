<?php

namespace App\Generators;

use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use Psr\Container\ContainerInterface;

class ComicIssuePagesGenerator extends ComicPagesGenerator
{
    private ComicIssueRepositoryInterface $comicIssueRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->comicIssueRepository = $container->comicIssueRepository;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['uri'] = 'comic_issues/{id:\d+}/pages';
        $options['filter'] = 'comic_issue_id';

        return $options;
    }

    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $comicId = $args['id'];

        $comic = $this->comicIssueRepository->get($comicId);

        $series = $comic->series();

        $params['source'] = "comic_issues/{$comicId}/pages";
        $params['breadcrumbs'] = [
            [
                'text' => 'Серии',
                'link' => $this->router->pathFor('admin.entities.comic_series')
            ],
            ['text' => $series->game()->name],
            [
                'text' => $series->nameRu,
                'link' => $this->router->pathFor(
                    'admin.entities.comic_issues',
                    ['id' => $series->getId()]
                )
            ],
            ['text' => $comic->numberStr()],
            ['text' => 'Страницы'],
        ];

        $params['hidden'] = [
            'comic_issue_id' => $comicId,
        ];

        return $params;
    }
}
