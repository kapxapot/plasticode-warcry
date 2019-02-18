<?php

namespace App\Controllers;

use Plasticode\RSS\FeedImage;
use Plasticode\RSS\FeedItem;
use Plasticode\RSS\RSSCreator20;
use Plasticode\Util\Sort;

use App\Models\ForumTopic;
use App\Models\Game;
use App\Models\News;
use App\Services\NewsAggregatorService;

class NewsController extends Controller
{
    private $newsAggregatorService;
    
    public function __construct($container)
    {
        parent::__construct($container);
        
        $this->newsAggregatorService = new NewsAggregatorService;
    }
    
	public function index($request, $response, $args)
	{
		if ($args['game']) {
			$game = Game::getPublishedByAlias($args['game']);
			
			if (!$game) {
				return $this->notFound($request, $response);
			}
		}

		$page = $request->getQueryParam('page', 1);
		$pageSize = $request->getQueryParam('pagesize', $this->getSettings('news_limit'));

		$news = $this->newsAggregatorService->getPage($game, $page, $pageSize);
		
		// paging
		$count = $this->newsAggregatorService->count($game);
		
		$url = $this->linker->game($game);
		
		$paging = $this->pagination->complex($url, $count, $page, $pageSize);

		$params = $this->buildParams([
			'game' => $game,
			'sidebar' => [ 'stream', 'gallery', 'events', 'articles' ],
			'params' => [
				'news' => $news,
				'paging' => $paging,
			],
		]);
		
		return $this->render($response, 'main/news/index.twig', $params);
	}

	public function item($request, $response, $args)
	{
		$id = $args['id'];
		$rebuild = $request->getQueryParam('rebuild', false);

		$forumNews = ForumTopic::getNews($id);
		$news = News::getProtected($id);
		
		if (!$forumNews && !$news) {
			return $this->notFound($request, $response);
		}
		
		if ($news && $rebuild) {
            $news->resetDescription();
        }

		$news = $news ?? $forumNews;

		$params = $this->buildParams([
			'game' => $news->game(),
			'sidebar' => [ 'stream', 'gallery', 'news', 'events' ],
			'news_id' => $id,
			'large_image' => $news->largeImage(),
			'image' => $news->image(),
			'params' => [
				'disqus_url' => $this->linker->disqusNews($id),
				'disqus_id' => 'news' . $id,
				'news_item' => $news,
				'title' => $news->displayTitle(),
				'page_description' => $this->makePageDescription($news->shortText, 'news.description_limit'),
			],
		]);
		
		return $this->view->render($response, 'main/news/item.twig', $params);
	}

	public function archiveIndex($request, $response, $args)
	{
		$years = $this->newsAggregatorService->getYears();
		
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

		$monthly = $this->newsAggregatorService->getByYear($year);
		
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
		
		$news = $this->newsAggregatorService->getTop($limit);

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
			$item->title = $n->displayTitle();
			$item->link = $this->linker->n($n->getId());
			$item->description = $this->parser->makeAbsolute($n->shortText());
			$item->date = $n->publishedAtIso();
			$item->author = $n->creator()->displayName();
			$item->category = array_map(function($t) {
				return $t->tag;
			}, $n->tagLinks());
			
			$rss->addItem($item);
		}
		
		$rss->saveFeed($fileName, true);
	}
}
