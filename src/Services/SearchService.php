<?php

namespace App\Services;

use Plasticode\Collection;
use Plasticode\Core\Linker;
use Plasticode\Models\Tag;

use App\Models\Article;
use App\Models\Event;
use App\Models\News;

class SearchService
{
    private $linker;
    
    public function __construct(Linker $linker)
    {
        $this->linker = $linker;
    }
    
    public function search($query)
    {
	    $articles = Article::search($query)
	        ->map(function ($article) {
	            return [
	                'type' => 'article',
	                'data' => $article->serialize(),
	                'text' => $article->nameRu,
	                'code' => $article->code(),
	                'url' => $this->linker->abs($article->url()),
	            ];
	        });
	    
	    $news = News::search($query)
	        ->map(function ($news) {
	            return [
	                'type' => 'news',
	                'data' => $news->serialize(),
	                'text' => $news->displayTitle(),
	                'code' => $news->code(),
	                'url' => $this->linker->abs($news->url()),
	            ];
	        });
	    
	    $events = Event::search($query)
	        ->map(function ($event) {
	            return [
	                'type' => 'event',
	                'data' => $event->serialize(),
	                'text' => $event->name,
	                'code' => $event->code(),
	                'url' => $this->linker->abs($event->url()),
	            ];
	        });
        
        $tags = Tag::search($query)
            ->distinct('tag')
            ->map(function ($tag) {
                return [
                    'type' => 'tag',
                    'text' => $tag->tag,
                    'code' => $tag->code(),
	                'url' => $this->linker->abs($tag->url()),
                ];
            });
        
	    return Collection::merge($articles, $news, $events, $tags);
    }
}
