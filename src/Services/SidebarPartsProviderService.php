<?php

namespace App\Services;

use App\Models\Game;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Exceptions\InvalidConfigurationException;

class SidebarPartsProviderService
{
    private SettingsProviderInterface $settingsProvider;

    private ArticleRepositoryInterface $articleRepository;
    private EventRepositoryInterface $eventRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;

    private NewsAggregatorService $newsAggregatorService;
    private StreamService $streamService;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        ArticleRepositoryInterface $articleRepository,
        EventRepositoryInterface $eventRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        NewsAggregatorService $newsAggregatorService,
        StreamService $streamService
    )
    {
        $this->settingsProvider = $settingsProvider;

        $this->articleRepository = $articleRepository;
        $this->eventRepository = $eventRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;

        $this->newsAggregatorService = $newsAggregatorService;
        $this->streamService = $streamService;
    }

    /**
     * @return mixed
     */
    public function getPart(array $settings, ?Game $game, string $part)
    {
        switch ($part) {
            case 'news':
                $limit = $this->settingsProvider
                    ->get('sidebar.latest_news_limit');

                $exceptNewsId = $settings['news_id'] ?? 0;

                return $this->newsAggregatorService
                    ->getLatest($game, $limit, $exceptNewsId);

            case 'articles':
                $limit = $this->settingsProvider
                    ->get('sidebar.article_limit');

                $exceptArticleId = $settings['article_id'] ?? 0;

                return $this->articleRepository->getLatestNewsByGame(
                    $game,
                    $limit,
                    $exceptArticleId
                );

            case 'stream':
                return [
                    'stream' => $this->streamService->topOnline($game),
                    'total_online' => $this->streamService->totalOnlineStr(),
                ];

            case 'gallery':
                $limit = $this->settingsProvider
                    ->get('sidebar.latest_gallery_pictures_limit');

                return $this->galleryPictureRepository
                    ->getAllByGame($game, $limit);

            case 'events':
                $days = $this->settingsProvider
                    ->get('sidebar.future_events_days');

                return $this->eventRepository
                    ->getAllCurrent($game, $days);

            case 'countdown':
                return $this->eventRepository
                    ->getAllFutureImportant()
                    ->first();
        }

        throw new InvalidConfigurationException(
            'No sidebart part defined: ' . $part
        );
    }
}
