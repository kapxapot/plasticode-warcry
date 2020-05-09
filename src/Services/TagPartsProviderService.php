<?php

namespace App\Services;

use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use App\Repositories\Interfaces\VideoRepositoryInterface;

class TagPartsProviderService
{
    private ArticleRepositoryInterface $articleRepository;
    private EventRepositoryInterface $eventRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;
    private VideoRepositoryInterface $videoRepository;

    private ComicService $comicService;
    private NewsAggregatorService $newsAggregatorService;
    private StreamService $streamService;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        EventRepositoryInterface $eventRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        VideoRepositoryInterface $videoRepository,
        ComicService $comicService,
        NewsAggregatorService $newsAggregatorService,
        StreamService $streamService
    )
    {
        $this->articleRepository = $articleRepository;
        $this->eventRepository = $eventRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;
        $this->videoRepository = $videoRepository;

        $this->comicService = $comicService;
        $this->newsAggregatorService = $newsAggregatorService;
        $this->streamService = $streamService;
    }

    public function getParts(string $tag) : array
    {
        $groups = [
            [
                'id' => 'news',
                'label' => 'Новости',
                'values' => $this->newsAggregatorService->getAllByTag($tag),
                'component' => 'news',
            ],
            [
                'id' => 'articles',
                'label' => 'Статьи',
                'values' => $this->articleRepository->getAllByTag($tag),
                'component' => 'articles',
            ],
            [
                'id' => 'events',
                'label' => 'События',
                'values' => $this->eventRepository->getAllByTag($tag),
                'component' => 'events',
            ],
            [
                'id' => 'gallery_pictures',
                'label' => 'Галерея',
                'values' => $this->galleryPictureRepository->getAllByTag($tag),
                'component' => 'gallery_pictures',
                'no_linkblock' => true,
            ],
            [
                'id' => 'comics',
                'label' => 'Комиксы',
                'values' => $this->comicService->getAllByTag($tag),
                'component' => 'comics',
                'no_linkblock' => true,
            ],
            [
                'id' => 'videos',
                'label' => 'Видео',
                'values' => $this->videoRepository->getAllByTag($tag),
                'component' => 'videos',
                'no_linkblock' => true,
            ],
            [
                'id' => 'streams',
                'label' => 'Стримы',
                'values' => $this->streamService->getArrangedByTag($tag),
                'component' => 'streams',
                'no_linkblock' => true,
            ],
        ];

        return array_filter(
            $groups,
            fn (array $a) => count($a['values']) > 0
        );
    }
}
