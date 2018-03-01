<?php

namespace App\Controllers;

use Plasticode\Controllers\Controller;

class BaseController extends Controller {
	protected $autoOneColumn = true;

	protected function buildParams($settings) {
		$params = parent::buildParams($settings);
		
		$games = $this->db->getGames();
		$game = $this->getGame($settings);
		
		$params['games'] = $this->builder->buildGames($games);
		$params['game'] = $this->builder->buildGame($game);

		return $params;
	}
	
	protected function getGame($settings) {
		return $settings['game'] ?? $this->db->getDefaultGame();
	}
	
	protected function buildMenu($settings) {
		$game = $this->getGame($settings);
		
		return $this->builder->buildMenuByGame($game);
	}

	protected function buildPart($settings, $result, $part) {
		$game = $settings['game'];

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
