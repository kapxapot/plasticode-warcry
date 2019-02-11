<?php

namespace App\Controllers;

use Plasticode\Controllers\Controller;

class BaseController extends Controller
{
	protected $defaultGame;

	public function __construct($container)
	{
		parent::__construct($container);
		
		$this->defaultGame = $this->db->getDefaultGame();
	}

	protected function buildParams($settings)
	{
		$params = parent::buildParams($settings);
		
		$games = $this->db->getGames();
		$game = $this->getGame($settings) ?? $this->defaultGame;
		$menuGame = $this->getMenuGame($settings);
		
		$params['games'] = $this->builder->buildGames($games);
		$params['game'] = $this->builder->buildGame($game);
		$params['menu_game'] = $this->builder->buildGame($menuGame);

		return $params;
	}
	
	protected function getGame($settings)
	{
	    return $this->db->getRootGame($settings['game']);
	}
	
	protected function getMenuGame($settings)
	{
	    $globalContext = $settings['global_context'] ?? false;
		if (!$globalContext) {
		    $game = $this->getGame($settings);
		}
		
		return $game ?? $this->defaultGame;
	}
	
	protected function buildMenu($settings)
	{
		$menuGame = $this->getMenuGame($settings);
		
		return $this->builder->buildMenuByGame($menuGame);
	}

	protected function buildPart($settings, $result, $part)
	{
		$game = $this->getGame($settings);

		switch ($part) {
			case 'news':
				$limit = $this->getSettings('sidebar.latest_news_limit');
				$exceptNewsId = $settings['news_id'] ?? null;
				
				$result[$part] = $this->builder->buildLatestNews($game, $limit, $exceptNewsId);
				break;
			
			case 'forum':
				$limit = $this->getSettings('sidebar.forum_topic_limit');
				$result[$part] = $this->builder->buildForumTopics($game, $limit);
				break;
			
			case 'articles':
				$limit = $this->getSettings('sidebar.article_limit');
				$exceptArticleId = $settings['article_id'] ?? null;
				
				$result[$part] = $this->builder->buildLatestArticles($game, $limit, $exceptArticleId);
				break;
			
			case 'stream':
				$result[$part] = $this->builder->buildOnlineStream($game);
				break;
			
			case 'gallery':
				$limit = $this->getSettings('sidebar.latest_gallery_pictures_limit');
				$result[$part] = $this->builder->buildLatestGalleryPictures($game, $limit);
				break;
			
			case 'events':
				$days = $this->getSettings('sidebar.future_events_days');
				$result[$part] = $this->builder->buildCurrentEvents($game, $days);
				break;

			default:
				$result = null;
				break;
		}
		
		return $result;
	}
}
