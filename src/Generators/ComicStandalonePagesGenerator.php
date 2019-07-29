<?php

namespace App\Generators;

use App\Models\ComicStandalone;

class ComicStandalonePagesGenerator extends ComicPagesBaseGenerator
{
    protected function getPageUrl(array $item) : string
    {
        $comic = ComicStandalone::get($item['comic_standalone_id']);
        $page = $comic->pageByNumber($item['number']);
        
        return $page->pageUrl();
    }
    
    public function getOptions() : array
    {
        $options = parent::getOptions();
        
        $options['uri'] = 'comic_standalones/{id:\d+}/comic_standalone_pages';
        $options['filter'] = 'comic_standalone_id';
        
        return $options;
    }

    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $comicId = $args['id'];
        
        $comic = ComicStandalone::get($comicId);

        $params['source'] = "comic_standalones/{$comicId}/comic_standalone_pages";
        $params['breadcrumbs'] = [
            [ 'text' => 'Комиксы', 'link' => $this->router->pathFor('admin.entities.comic_standalones') ],
            [ 'text' => $comic->game()->name ],
            [ 'text' => $comic->nameRu ],
            [ 'text' => 'Страницы' ],
        ];
        
        $params['hidden'] = [
            'comic_standalone_id' => $comicId,
        ];
        
        return $params;
    }
}
