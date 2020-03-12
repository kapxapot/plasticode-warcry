<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryPicture;
use Plasticode\Core\Interfaces\SettingsProviderInterface;

class SidebarPartsProviderService
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var NewsAggregatorService */
    private $newsAggregatorService;

    /** @var StreamService */
    private $streamService;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        NewsAggregatorService $newsAggregatorService,
        StreamService $streamService
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->newsAggregatorService = $newsAggregatorService;
        $this->streamService = $streamService;
    }

    public function getPart($settings, $game, $part)
    {
        $result = null;
        
        switch ($part) {
            case 'news':
                $limit = $this->settingsProvider
                    ->get('sidebar.latest_news_limit');
                
                $exceptNewsId = $settings['news_id'] ?? null;
                
                $result = $this->newsAggregatorService
                    ->getLatest($game, $limit, $exceptNewsId);
                
                break;

            case 'articles':
                $limit = $this->settingsProvider
                    ->get('sidebar.article_limit');
                
                $exceptArticleId = $settings['article_id'] ?? null;
                
                $result = Article::getLatest(
                    $game,
                    $limit,
                    $exceptArticleId
                )->all();

                break;
            
            case 'stream':
                $result = [
                    'stream' => $this->streamService->topOnline($game),
                    'total_online' => $this->streamService->totalOnlineStr(),
                ];

                break;
            
            case 'gallery':
                $limit = $this->settingsProvider
                    ->get('sidebar.latest_gallery_pictures_limit');

                $result = [
                    'pictures' => GalleryPicture::getLatestByGame(
                        $game,
                        $limit
                    )->all()
                ];

                break;
            
            case 'events':
                $days = $this->settingsProvider
                    ->get('sidebar.future_events_days');
                
                $result = Event::getCurrent($game, $days)->all();

                break;
            
            case 'countdown':
                $event = Event::getFutureImportant()->one();
                $result = ['event' => $event];
                
                break;
        }
        
        return $result;
    }
}
