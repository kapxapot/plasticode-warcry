<?php

namespace App\Generators;

use App\Models\Game;

class ComicPagesGenerator extends ComicPagesBaseGenerator
{
    protected function getPageUrl($item)
    {
        $comic = $this->db->getComicIssue($item['comic_issue_id']);
        if ($comic) {
    		$series = $this->db->getComicSeries($comic['series_id']);
		    if ($series) {
        		return $this->linker->comicIssuePage($series['alias'], $comic['number'], $item['number']);
		    }
        }
    }
    
	public function getOptions()
	{
	    $options = parent::getOptions();
	    
	    $options['uri'] = 'comic_issues/{id:\d+}/comic_pages';
	    $options['filter'] = 'comic_issue_id';
	    
	    return $options;
	}
	
	public function getAdminParams($args)
	{
		$params = parent::getAdminParams($args);

		$comicId = $args['id'];
		$comic = $this->db->getComicIssue($comicId);
		$seriesId = $comic['series_id'];
		$series = $this->db->getComicSeries($seriesId, true);
		$game = Game::get($series['game_id']);

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
