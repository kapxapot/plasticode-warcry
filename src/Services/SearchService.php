<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Event;
use App\Models\News;
use Plasticode\Collection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class SearchService
{
    /** @var TagRepositoryInterface */
    private $tagRepository;

    /** @var LinkerInterface $linker */
    private $linker;
    
    public function __construct(
        TagRepositoryInterface $tagRepository,
        LinkerInterface $linker
    )
    {
        $this->tagRepository = $tagRepository;
        $this->linker = $linker;
    }
    
    public function search($query)
    {
        $articles = Article::search($query)
            ->map(
                function (Article $article) {
                    return [
                        'type' => 'article',
                        'data' => $article->serialize(),
                        'text' => $article->nameRu,
                        'code' => $article->code(),
                        'url' => $this->linker->abs($article->url()),
                    ];
                }
            );
        
        $news = News::search($query)
            ->map(
                function (News $news) {
                    return [
                        'type' => 'news',
                        'data' => $news->serialize(),
                        'text' => $news->displayTitle(),
                        'code' => $news->code(),
                        'url' => $this->linker->abs($news->url()),
                    ];
                }
            );
        
        $events = Event::search($query)
            ->map(
                function (Event $event) {
                    return [
                        'type' => 'event',
                        'data' => $event->serialize(),
                        'text' => $event->name,
                        'code' => $event->code(),
                        'url' => $this->linker->abs($event->url()),
                    ];
                }
            );
        
        $tags = $this->tagRepository->search($query)
            ->distinct('tag')
            ->map(
                function (Tag $tag) {
                    return [
                        'type' => 'tag',
                        'text' => $tag->tag,
                        'code' => $tag->code(),
                        'url' => $this->linker->abs($tag->url()),
                    ];
                }
            );
        
        return Collection::merge($articles, $news, $events, $tags);
    }
}
