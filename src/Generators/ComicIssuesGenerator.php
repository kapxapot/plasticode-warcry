<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;

use App\Data\Taggable;

class ComicIssuesGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::COMIC_ISSUES;
	
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
		$data = $this->publishIfNeeded($data);		
		
		if (($data['number'] ?? 0) <= 0) {
		    $seriesId = $data['series_id'];
		    $data['number'] = $this->db->getMaxComicIssueNumber($seriesId, $id) + 1;
		}

		return $data;
	}
	
	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);
	    
		$series = $this->db->getComicSeries($item['series_id']);
		if ($series) {
			$item['series_alias'] = $series['alias'];
    		$item['page_url'] = $this->linker->comicIssue($series['alias'], $item['number']);
		}
		
		$item['context_field'] = 'comic_issue_id';

		return $item;
	}

	public function getAdminParams($args)
	{
		$params = parent::getAdminParams($args);
		
		$seriesId = $args['id'];
		$series = $this->db->getComicSeries($seriesId, true);
		$game = $this->db->getGame($series['game_id']);
		
		$params['source'] = "comic_series/{$seriesId}/comic_issues";
		$params['breadcrumbs'] = [
			[ 'text' => 'Серии', 'link' => $this->router->pathFor('admin.entities.comic_series') ],
			[ 'text' => $game ? $game['name'] : '(нет игры)' ],
			[ 'text' => $series['name_ru'] ],
			[ 'text' => 'Выпуски' ],
		];
		
		$params['hidden'] = [
			'series_id' => $seriesId,
		];
		
		return $params;
	}
}
