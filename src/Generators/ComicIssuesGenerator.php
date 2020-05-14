<?php

namespace App\Generators;

use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use App\Repositories\Interfaces\ComicSeriesRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;

class ComicIssuesGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private ComicIssueRepositoryInterface $comicIssueRepository;
    private ComicSeriesRepositoryInterface $comicSeriesRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->comicIssueRepository = $container->comicIssueRepository;
        $this->comicSeriesRepository = $container->comicSeriesRepository;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['uri'] = 'comic_series/{id:\d+}/comic_issues';
        $options['filter'] = 'series_id';
        $options['admin_template'] = 'entity_with_upload';
        $options['admin_args'] = [
            'upload_path' => 'admin.comics.issue.upload',
        ];

        return $options;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);
        $data = $this->publishIfNeeded($data);

        if (($data['number'] ?? 0) <= 0) {
            $seriesId = $data['series_id'];

            $series = $this->comicSeriesRepository->get($seriesId);

            if ($series) {
                $data['number'] = $series->maxIssueNumber() + 1;
            }
        }

        return $data;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $comic = $this->comicIssueRepository->get($id);

        $series = $comic->series();

        if ($series) {
            $item['series_alias'] = $series->alias;
        }

        $item['page_url'] = $comic->pageUrl();
        $item['context_field'] = 'comic_issue_id';

        return $item;
    }

    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $seriesId = $args['id'];

        $series = $this->comicSeriesRepository->get($seriesId);

        $game = $series->game();

        $params['source'] = 'comic_series/' . $seriesId . '/comic_issues';

        $params['breadcrumbs'] = [
            [
                'text' => 'Серии',
                'link' => $this->router->pathFor('admin.entities.comic_series')
            ],
            ['text' => $game ? $game->name : '(нет игры)'],
            ['text' => $series->name()],
            ['text' => 'Выпуски'],
        ];

        $params['hidden'] = [
            'series_id' => $seriesId,
        ];

        return $params;
    }
}
