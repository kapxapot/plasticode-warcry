<?php

namespace App\Services;

use Plasticode\Contained;

use App\Models\Article;
use App\Models\Event;
use App\Models\ForumTopic;
use App\Models\GalleryPicture;
use App\Models\Stream;

class SidebarPartsProviderService extends Contained
{
    private $newsAggregatorService;
    
    public function __construct($container, NewsAggregatorService $newsAggregatorService)
    {
        parent::__construct($container);
        
        $this->newsAggregatorService = $newsAggregatorService;
    }
    
    public function getPart($settings, $game, $part)
    {
        $result = null;
        
		switch ($part) {
			case 'news':
				$limit = $this->getSettings('sidebar.latest_news_limit');
				$exceptNewsId = $settings['news_id'] ?? null;
				
				$result = $this->newsAggregatorService->getLatest($game, $limit, $exceptNewsId);
				break;

			case 'articles':
				$limit = $this->getSettings('sidebar.article_limit');
				$exceptArticleId = $settings['article_id'] ?? null;
				
				$result = Article::getLatest($game, $limit, $exceptArticleId);
				break;
			
			case 'stream':
				$result = [
				    'stream' => Stream::topOnline($game),
				    'total_online' => Stream::totalOnlineStr(),
				];
				break;
			
			case 'gallery':
				$limit = $this->getSettings('sidebar.latest_gallery_pictures_limit');
				$result = [ 'pictures' => GalleryPicture::getLatestByGame($game, $limit) ];
				break;
			
			case 'events':
				$days = $this->getSettings('sidebar.future_events_days');
				$result = Event::getCurrent($game, $days);
				break;
		}
		
		return $result;
    }
}
