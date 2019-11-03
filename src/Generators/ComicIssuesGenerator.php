<?php

namespace App\Generators;

use App\Models\ComicIssue;
use App\Models\ComicSeries;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;

class ComicIssuesGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();
        
        $options['uri'] = 'comic_series/{id:\d+}/comic_issues';
        $options['filter'] = 'series_id';
        $options['admin_template'] = 'entity_with_upload';
        $options['admin_args'] = [
            'upload_path' => 'admin.comics.upload',
        ];

        return $options;
    }
    
    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);
        
        $data = $this->publishIfNeeded($data);
        
        if (($data['number'] ?? 0) <= 0) {
            $series = ComicSeries::get($data['series_id']);
            
            if ($series) {
                $data['number'] = $series->maxIssueNumber($id) + 1;
            }
        }

        return $data;
    }
    
    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $comic = new ComicIssue($item);
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
        $series = ComicSeries::get($seriesId);
        $game = $series->game();
        
        $params['source'] = "comic_series/{$seriesId}/comic_issues";
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
