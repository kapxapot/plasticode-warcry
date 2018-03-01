<?php

namespace App\Generators;

class ComicPagesGenerator extends ComicPagesBaseGenerator {
	public function getOptions() {
		return [
			'uri' => 'comic_issues/{id:\d+}/comic_pages',
			'filter' => 'comic_issue_id',
		];
	}
	
	public function getAdminParams($args) {
		$params = parent::getAdminParams($args);

		$comicId = $args['id'];
		$comic = $this->db->getComicIssue($comicId);
		$seriesId = $comic['series_id'];
		$series = $this->db->getComicSeries($seriesId);
		$game = $this->db->getGame($series['game_id']);

		$params['source'] = "comic_issues/{$comicId}/comic_pages";
		$params['breadcrumbs'] = [
			[ 'text' => 'Серии', 'link' => $this->router->pathFor('admin.entities.comic_series') ],
			[ 'text' => $game ? $game['name'] : '(нет игры)' ],
			[ 'text' => $series['name_ru'], 'link' => $this->router->pathFor('admin.entities.comic_issues', [ 'id' => $seriesId ]) ],
			[ 'text' => '#' . $comic['number'] . ($comic['name_ru'] ? ': ' . $comic['name_ru'] : '') ],
			[ 'text' => 'Страницы' ],
		];
		
		$params['hidden'] = [
			'comic_issue_id' => $comicId,
		];
		
		return $params;
	}
}
