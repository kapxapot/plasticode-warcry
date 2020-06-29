<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Models\Interfaces\NewsSourceInterface;
use App\Services\NewsAggregatorService;
use Plasticode\Core\Pagination;
use Plasticode\RSS\FeedImage;
use Plasticode\RSS\FeedItem;
use Plasticode\RSS\RSSCreator20;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Plasticode\IO\File;
use Plasticode\TagLink;
use Plasticode\Util\Text;
use Psr\Container\ContainerInterface;
use Slim\Http\Request as SlimRequest;

class NewsController extends NewsSourceController
{
    private NewsAggregatorService $newsAggregatorService;
    private Pagination $pagination;
    private NotFoundHandler $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->newsAggregatorService = $container->newsAggregatorService;
        $this->pagination = $container->pagination;
        $this->notFoundHandler = $container->notFoundHandler;
    }

    public function index(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $game = null;
        $gameAlias = $args['game'] ?? null;

        if ($gameAlias) {
            $game = $this
                ->gameRepository
                ->getPublishedByAlias($gameAlias);

            if (is_null($game)) {
                return ($this->notFoundHandler)($request, $response);
            }
        }

        $page = $request->getQueryParam('page', 1);

        $pageSize = $request->getQueryParam(
            'pagesize',
            $this->getSettings('news.limit', 7)
        );

        $news = $this
            ->newsAggregatorService
            ->getPage($game, $page, $pageSize);

        $count = $this->newsAggregatorService->getCount($game);

        $url = $this->linker->game($game);

        $paging = $this->pagination->complex($url, $count, $page, $pageSize);

        $params = $this->buildParams(
            [
                'game' => $game,
                'sidebar' => [
                    'countdown',
                    'stream',
                    'gallery',
                    'events',
                    'articles',
                ],
                'params' => [
                    'news' => $news,
                    'paging' => $paging,
                ],
            ]
        );

        return $this->render($response, 'main/news/index.twig', $params);
    }

    public function item(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $rebuild = $request->getQueryParam('rebuild', null);

        $news = $this->newsAggregatorService->getNews($id);

        if (!$news) {
            return ($this->notFoundHandler)($request, $response);
        }

        if ($rebuild !== null) {
            // Todo: reset news description, if applicable
            // Currently, there's no caching
        }

        $prev = $this->newsAggregatorService->getPrev($news);
        $next = $this->newsAggregatorService->getNext($news);

        $params = $this->buildParams(
            [
                'game' => $news->game(),
                'sidebar' => ['stream', 'gallery', 'news', 'events'],
                'news_id' => $id,
                'large_image' => $news->largeImage(),
                'image' => $news->image(),
                'params' => [
                    'news_item' => $news,
                    'title' => $news->displayTitle(),
                    'page_description' => $this->makeNewsPageDescription(
                        $news,
                        'news.description_limit'
                    ),
                    'news_prev' => $prev,
                    'news_next' => $next,
                    'rel_prev' => $prev ? $prev->url() : null,
                    'rel_next' => $next ? $next->url() : null,
                    'canonical_url' => $this->linker->abs($news->url),
                    'disqus_id' => 'news' . $id,
                ],
            ]
        );

        return $this->render($response, 'main/news/item.twig', $params);
    }

    public function archiveIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $years = $this->newsAggregatorService->getYears();

        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery'],
                'params' => [
                    'title' => 'Архив новостей',
                    'years' => $years,
                ],
            ]
        );

        return $this->render($response, 'main/news/archive/index.twig', $params);
    }

    public function archiveYear(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $year = $args['year'];

        $monthly = $this->newsAggregatorService->getByYear($year);

        $prev = $this->newsAggregatorService->getPrevYear($year);
        $next = $this->newsAggregatorService->getNextYear($year);

        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery'],
                'params' => [
                    'title' => 'Архив новостей за ' . $year . ' год',
                    'archive_year' => $year,
                    'monthly' => $monthly,
                    'year_prev' => $prev,
                    'year_next' => $next,
                    'rel_prev' => $prev ? $prev->url() : null,
                    'rel_next' => $next ? $next->url() : null,
                ],
            ]
        );

        return $this->render($response, 'main/news/archive/year.twig', $params);
    }

    public function rss(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $limit = $this->getSettings('news.rss_limit' ?? 10);

        $news = $this->newsAggregatorService->getTop($limit);

        $path = $this->getSettings('folders.rss_cache');
        $fileName = File::combine(__DIR__, $path, 'rss.xml');

        $settings = $this->getSettings('view_globals');

        $siteUrl = $settings['site_url'];
        $siteName = $settings['site_name'];
        $siteDescription = $settings['site_description'];
        $teamMail = $settings['team_mail'];

        $rss = new RSSCreator20($response);

        if (!$rss->useCached($fileName, 300)) {
            $rss->title = $siteName;
            $rss->description = $siteDescription;
            $rss->link = $siteUrl;
            $rss->syndicationURL = $this->router->pathFor('main.rss');
            $rss->encoding = 'utf-8';
            $rss->language = 'ru';
            $rss->copyright = $siteName;
            $rss->webmaster = $teamMail;
            $rss->ttl = 300;

            $image = new FeedImage();
            $image->title = $siteName . ' logo';
            $image->url = $siteUrl . $settings['logo'];
            $image->link = $siteUrl;
            $image->description = $siteDescription;
            $rss->image = $image;

            /** @var NewsSourceInterface */
            foreach ($news as $n) {
                $item = new FeedItem();
                $item->title = $n->displayTitle();
                $item->link = $this->linker->abs($n->url());

                $item->description = Text::toAbsoluteUrls(
                    $n->shortText(),
                    $this->linker->abs()
                );

                $item->date = $n->publishedAtIso();
                $item->author = $n->creator()->displayName();
                $item->category = array_map(
                    fn (TagLink $t) => $t->tag,
                    $n->tagLinks()->toArray()
                );

                $rss->addItem($item);
            }

            $rss->saveFeed($fileName, true);
        }

        return $rss->getResponse();
    }
}
