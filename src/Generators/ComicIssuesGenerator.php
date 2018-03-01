<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;

class ComicIssuesGenerator extends EntityGenerator {
	public function getOptions() {
		return [
			'uri' => 'comic_series/{id:\d+}/comic_issues',
			'filter' => 'series_id',
		];
	}
	
	public function afterLoad($item) {
		$series = $this->db->getComicSeries($item['series_id']);
		if ($series) {
			$item['series_alias'] = $series['alias'];
		}

		return $item;
	}
	
	public function getAdminParams($args) {
		$params = parent::getAdminParams($args);
		
		$seriesId = $args['id'];
		$series = $this->db->getComicSeries($seriesId);
		$game = $this->db->getGame($series['game_id']);
		
		$params['source'] = "comic_series/{$seriesId}/comic_issues";
		$params['breadcrumbs'] = [
			[ 'text' => 'Серии', 'link' => $this->router->pathFor('admin.entities.comic_series') ],
			[ 'text' => $game ? $game['name'] : '(нет игры)' ],
			[ 'text' => $series['name_ru'] ],
			[ 'text' => 'Комиксы' ],
		];
		
		$params['hidden'] = [
			'series_id' => $seriesId,
		];
		
		return $params;
	}
}
