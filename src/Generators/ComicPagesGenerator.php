<?php

namespace App\Generators;

use App\Models\ComicIssue;

class ComicPagesGenerator extends ComicPagesBaseGenerator
{
    protected function getPageUrl(array $item) : string
    {
        $comic = ComicIssue::get($item['comic_issue_id']);
        $page = $comic->pageByNumber($item['number']);

        return $page->pageUrl();
    }
    
    public function getOptions() : array
    {
        $options = parent::getOptions();
        
        $options['uri'] = 'comic_issues/{id:\d+}/comic_pages';
        $options['filter'] = 'comic_issue_id';
        
        return $options;
    }
    
    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $comicId = $args['id'];
        
        $comic = ComicIssue::get($comicId);
        $series = $comic->series();

        $params['source'] = "comic_issues/{$comicId}/comic_pages";
        $params['breadcrumbs'] = [
            [ 'text' => 'Серии', 'link' => $this->router->pathFor('admin.entities.comic_series') ],
            [ 'text' => $series->game()->name ],
            [ 'text' => $series->nameRu, 'link' => $this->router->pathFor('admin.entities.comic_issues', [ 'id' => $series->getId() ]) ],
            [ 'text' => '#' . $comic->number . ($comic->nameRu ? ': ' . $comic->nameRu : '') ],
            [ 'text' => 'Страницы' ],
        ];
        
        $params['hidden'] = [
            'comic_issue_id' => $comicId,
        ];
        
        return $params;
    }
}
