<?php

namespace App\Generators;

class ComicStandalonePagesGenerator extends ComicPagesBaseGenerator
{
    protected function getPageUrl($item)
    {
        $comic = $this->db->getComicStandalone($item['comic_standalone_id']);
        if ($comic) {
            return $this->linker->comicStandalonePage($comic['alias'], $item['number']);
        }
    }
    
	public function getOptions()
	{
	    $options = parent::getOptions();
	    
	    $options['uri'] = 'comic_standalones/{id:\d+}/comic_standalone_pages';
	    $options['filter'] = 'comic_standalone_id';
	    
	    return $options;
	}

	public function getAdminParams($args)
	{
		$params = parent::getAdminParams($args);

		$comicId = $args['id'];
		$comic = $this->db->getComicStandalone($comicId, true);
		$game = $this->db->getGame($comic['game_id']);

		$params['source'] = "comic_standalones/{$comicId}/comic_standalone_pages";
		$params['breadcrumbs'] = [
			[ 'text' => 'Комиксы', 'link' => $this->router->pathFor('admin.entities.comic_standalones') ],
			[ 'text' => $game ? $game['name'] : '(нет игры)' ],
			[ 'text' => $comic['name_ru'] ],
			[ 'text' => 'Страницы' ],
		];
		
		$params['hidden'] = [
			'comic_standalone_id' => $comicId,
		];
		
		return $params;
	}
}
