<?php

namespace App\Controllers;

use Plasticode\RSS\FeedImage;
use Plasticode\RSS\FeedItem;
use Plasticode\RSS\RSSCreator20;
use Plasticode\Util\Sort;

class NewsController extends BaseController
{
	public function index($request, $response, $args)
	{
		if ($args['game']) {
			$filterByGame = $this->db->getGameByAlias($args['game']);
			
			if (!$filterByGame) {
				return $this->notFound($request, $response);
			}
		}

		$page = $request->getQueryParam('page', 1);
		$pageSize = $request->getQueryParam('pagesize', $this->getSettings('news_limit'));

		$news = $this->builder->buildAllNews($filterByGame, $page, $pageSize);
		
		// paging
		$count = $this->db->getAllNewsCount($filterByGame);
		$url = $this->linker->game($filterByGame);
		
		$paging = $this->builder->buildComplexPaging($url, $count, $page, $pageSize);

		$params = $this->buildParams([
			'game' => $filterByGame,
			'sidebar' => [ 'stream', 'gallery', 'events', 'articles' ],
			'params' => [
				'news' => $news,
				'paging' => $paging,
			],
		]);
		
		return $this->view->render($response, 'main/news/index.twig', $params);
	}

	public function item($request, $response, $args)
	{
		$id = $args['id'];
		$rebuild = $request->getQueryParam('rebuild', false);

		$forumNewsRow = $this->db->getForumNews($id);
		$newsRow = $this->db->getNews($id);
		
		if (!$forumNewsRow && !$newsRow) {
			return $this->notFound($request, $response);
		}

		$news = $forumNewsRow
			? $this->builder->buildForumNews($forumNewsRow, true)
			: $this->builder->buildNews($newsRow, true, $rebuild);

		$params = $this->buildParams([
			'game' => $news['game'],
			'sidebar' => [ 'stream', 'gallery', 'news', 'events' ],
			'news_id' => $id,
			'large_image' => $news['parsed']['large_image'],
			'image' => $news['parsed']['image'],
			'params' => [
				'disqus_url' => $this->linker->disqusNews($id),
				'disqus_id' => 'news' . $id,
				'news_item' => $news,
				'title' => $news['title'],
				'page_description' => $news['description'],
			],
		]);
		
		return $this->view->render($response, 'main/news/item.twig', $params);
	}

	public function archiveIndex($request, $response, $args)
	{
		$years = $this->builder->buildNewsYears();
		
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => 'Архив новостей', 
				'years' => $years,
			],
		]);
	
		return $this->view->render($response, 'main/news/archive/index.twig', $params);
	}
	
	public function archiveYear($request, $response, $args)
	{
		$year = $args['year'];

		$monthly = $this->builder->buildNewsArchive($year);
		
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => "Архив новостей за {$year} год", 
				'archive_year' => $year,
				'monthly' => $monthly,
			],
		]);
	
		return $this->view->render($response, 'main/news/archive/year.twig', $params);
	}
	
	public function rss($request, $response, $args)
	{
		$limit = $this->getSettings('rss_limit');
		
		$news = $this->builder->buildAllNews(null, 1, $limit);

		$fileName = __DIR__ . $this->getSettings('folders.rss_cache') . 'rss.xml';

		$settings = $this->getSettings('view_globals');
		
		$siteUrl = $settings['site_url'];
		$siteName = $settings['site_name'];
		$siteDescription = $settings['site_description'];
		$teamMail = $settings['team_mail'];
		
		$rss = new RSSCreator20();
		$rss->useCached($fileName, 300);
		$rss->title = $siteName;
		$rss->description = $siteDescription;
		$rss->link = $siteUrl;
		$rss->syndicationURL = $siteUrl . '/rss';
		$rss->encoding = "utf-8";
		$rss->language = 'ru';
		$rss->copyright = $siteName;
		$rss->webmaster = $teamMail;
		$rss->ttl = 300;
		
		$image = new FeedImage();
		$image->title = $siteName . " logo";
		$image->url = $siteUrl . $settings['logo'];
		$image->link = $siteUrl;
		$image->description = $siteDescription;
		$rss->image = $image;

		foreach ($news as $n) {
			$item = new FeedItem();
			$item->title = $n['title'];
			$item->link = $this->linker->n($n['id']);
			$item->description = $n['text'];
			$item->date = $n['pub_date'];
			$item->author = $n['starter_name'];
			$item->category = array_map(function($t) {
				return $t['text'];
			}, $n['tags']);
			
			$rss->addItem($item);
		}
		
		$rss->saveFeed($fileName, true);
	}
}
