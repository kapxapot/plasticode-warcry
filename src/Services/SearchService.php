<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Event;
use App\Models\News;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Collections\Basic\ArrayCollection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class SearchService
{
    private ArticleRepositoryInterface $articleRepository;
    private EventRepositoryInterface $eventRepository;
    private NewsRepositoryInterface $newsRepository;
    private TagRepositoryInterface $tagRepository;

    private LinkerInterface $linker;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        EventRepositoryInterface $eventRepository,
        NewsRepositoryInterface $newsRepository,
        TagRepositoryInterface $tagRepository,
        LinkerInterface $linker
    )
    {
        $this->articleRepository = $articleRepository;
        $this->eventRepository = $eventRepository;
        $this->newsRepository = $newsRepository;
        $this->tagRepository = $tagRepository;

        $this->linker = $linker;
    }

    public function search($query) : ArrayCollection
    {
        $articles = $this
            ->articleRepository
            ->search($query)
            ->map(
                fn (Article $a) =>
                [
                    'type' => 'article',
                    'data' => $a->serialize(),
                    'text' => $a->nameRu,
                    'code' => $a->code(),
                    'url' => $this->linker->abs($a->url()),
                ]
            );

        $news = $this
            ->newsRepository
            ->search($query)
            ->map(
                fn (News $n) =>
                [
                    'type' => 'news',
                    'data' => $n->serialize(),
                    'text' => $n->displayTitle(),
                    'code' => $n->code(),
                    'url' => $this->linker->abs($n->url()),
                ]
            );

        $events = $this
            ->eventRepository
            ->search($query)
            ->map(
                fn (Event $e) =>
                [
                    'type' => 'event',
                    'data' => $e->serialize(),
                    'text' => $e->name,
                    'code' => $e->code(),
                    'url' => $this->linker->abs($e->url()),
                ]
            );

        $tags = $this
            ->tagRepository
            ->search($query)
            ->distinctBy('tag')
            ->map(
                fn (Tag $t) =>
                [
                    'type' => 'tag',
                    'text' => $t->tag,
                    'code' => $t->code(),
                    'url' => $this->linker->abs($t->url()),
                ]
            );

        return ArrayCollection::merge(
            ArrayCollection::from($articles),
            ArrayCollection::from($news),
            ArrayCollection::from($events),
            ArrayCollection::from($tags)
        );
    }
}
