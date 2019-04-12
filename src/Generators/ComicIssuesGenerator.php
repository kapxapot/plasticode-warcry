<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

use App\Models\ComicIssue;
use App\Models\ComicSeries;

class ComicIssuesGenerator extends TaggableEntityGenerator
{
	use Publishable;

	public function getOptions()
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
	
	public function beforeSave($data, $id = null)
	{
	    $data = parent::beforeSave($data, $id);
	    
		$data = $this->publishIfNeeded($data);		
		
		if (($data['number'] ?? 0) <= 0) {
		    $series = ComicSeries::get($data['series_id']);
		    
		    if ($series) {
		        $data['number'] = $series->maxIssueNumber($id) + 1;
		    }
		}

		return $data;
	}
	
	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);
	    
	    $comic = new ComicIssue($item);
		$series = $comic->series();
		
		if ($series) {
			$item['series_alias'] = $series->alias;
		}
		
		$item['page_url'] = $this->linker->comicIssue($comic);
		$item['context_field'] = 'comic_issue_id';

		return $item;
	}

	public function getAdminParams($args)
	{
		$params = parent::getAdminParams($args);
		
		$seriesId = $args['id'];
		$series = ComicSeries::get($seriesId);
		$game = $series->game();
		
		$params['source'] = "comic_series/{$seriesId}/comic_issues";
		$params['breadcrumbs'] = [
			[ 'text' => 'Серии', 'link' => $this->router->pathFor('admin.entities.comic_series') ],
			[ 'text' => $game ? $game->name : '(нет игры)' ],
			[ 'text' => $series->name() ],
			[ 'text' => 'Выпуски' ],
		];
		
		$params['hidden'] = [
			'series_id' => $seriesId,
		];
		
		return $params;
	}
}
