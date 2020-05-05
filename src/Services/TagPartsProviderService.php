<?php

namespace App\Services;

use App\Models\ComicIssue;
use App\Models\ComicSeries;
use App\Models\ComicStandalone;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Plasticode\Collections\Basic\Collection;

class TagPartsProviderService
{
    private ArticleRepositoryInterface $articleRepository;
    private EventRepositoryInterface $eventRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;
    private VideoRepositoryInterface $videoRepository;

    private NewsAggregatorService $newsAggregatorService;
    private StreamService $streamService;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        EventRepositoryInterface $eventRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        VideoRepositoryInterface $videoRepository,
        GalleryService $galleryService,
        NewsAggregatorService $newsAggregatorService,
        StreamService $streamService
    )
    {
        $this->articleRepository = $articleRepository;
        $this->eventRepository = $eventRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;
        $this->videoRepository = $videoRepository;

        $this->galleryService = $galleryService;
        $this->newsAggregatorService = $newsAggregatorService;
        $this->streamService = $streamService;
    }

    public function getParts(string $tag) : array
    {
        $groups = [
            [
                'id' => 'news',
                'label' => 'Новости',
                'values' => $this->newsAggregatorService->getByTag($tag),
                'component' => 'news',
            ],
            [
                'id' => 'articles',
                'label' => 'Статьи',
                'values' => $this->articleRepository->getNewsByTag($tag),
                'component' => 'articles',
            ],
            [
                'id' => 'events',
                'label' => 'События',
                'values' => $this->eventRepository->getNewsByTag($tag),
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
                'values' => Collection::merge(
                    ComicIssue::getByTag($tag)->all(),
                    ComicSeries::getByTag($tag)->all(),
                    ComicStandalone::getByTag($tag)->all()
                ),
                'component' => 'comics',
                'no_linkblock' => true,
            ],
            [
                'id' => 'videos',
                'label' => 'Видео',
                'values' => $this->videoRepository->getNewsByTag($tag),
                'component' => 'videos',
                'no_linkblock' => true,
            ],
            [
                'id' => 'streams',
                'label' => 'Стримы',
                'values' => $this->streamService->getByTag($tag),
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
