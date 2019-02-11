<?php

namespace App\Controllers;

use App\Models\ComicSeries;
use App\Models\ComicIssue;
use App\Models\ComicStandalone;
use App\Models\ComicPage;
use App\Models\ComicStandalonePage;

class ComicController extends Controller
{
	private $comicsTitle;
	
	public function __construct($container)
	{
		parent::__construct($container);

		$this->comicsTitle = $this->getSettings('comics.title');
	}

	public function index($request, $response, $args)
	{
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => $this->comicsTitle,
				'series' => ComicSeries::getAllSorted(),
				'standalones' => ComicStandalone::getAllPublished(),
			],
		]);
	
		return $this->view->render($response, 'main/comics/index.twig', $params);
	}

	public function series($request, $response, $args)
	{
		$alias = $args['alias'];
		
		$series = ComicSeries::getPublishedByAlias($alias);

		if (!$series) {
			return $this->notFound($request, $response);
		}
		
		$params = $this->buildParams([
			'game' => $series->game(),
			'global_context' => true,
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'large_image' => $series->cover() ? $this->linker->abs($series->cover()->url()) : null,
			'description' => $series->parsedDescription(),
			'params' => [
				'series' => $series,
				'comics' => $series->issues(),
				'title' => $series->fullName(),
				'comics_title' => $this->comicsTitle,
			],
		]);

		return $this->view->render($response, 'main/comics/series.twig', $params);
	}
	
	public function issue($request, $response, $args)
	{
		$alias = $args['alias'];
		$number = $args['number'];

		$series = ComicSeries::getPublishedByAlias($alias);

		if (!$series) {
			return $this->notFound($request, $response);
		}
		
		$comic = $series->issueByNumber($number);
		
		if (!$comic) {
			return $this->notFound($request, $response);
		}

		$params = $this->buildParams([
			'game' => $series->game(),
			'global_context' => true,
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'large_image' => $comic->cover() ? $this->linker->abs($comic->cover()->url()) : null,
			'description' => $comic->parsedDescription(),
			'params' => [
				'series' => $series,
				'comic' => $comic,
				'pages' => $comic->pages(),
				'title' => $comic->titleName(),
				'comics_title' => $this->comicsTitle,
				'rel_prev' => $comic->prev() ? $comic->prev()->pageUrl() : null,
				'rel_next' => $comic->next() ? $comic->next()->pageUrl() : null,
			],
		]);

		return $this->view->render($response, 'main/comics/issue.twig', $params);
	}
	
	public function standalone($request, $response, $args)
	{
		$alias = $args['alias'];

		$comic = ComicStandalone::getPublishedByAlias($alias);

		if (!$comic) {
			return $this->notFound($request, $response);
		}

		$params = $this->buildParams([
			'game' => $comic->game(),
			'global_context' => true,
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'large_image' => $comic->cover() ? $this->linker->abs($comic->cover()->url()) : null,
			'description' => $comic->parsedDescription(),
			'params' => [
				'comic' => $comic,
				'pages' => $comic->pages(),
				'title' => $comic->titleName(),
				'comics_title' => $this->comicsTitle,
			],
		]);

		return $this->view->render($response, 'main/comics/standalone.twig', $params);
	}
	
	public function issuePage($request, $response, $args)
	{
		$alias = $args['alias'];
		$comicNumber = $args['number'];
		$pageNumber = $args['page'];

		$series = ComicSeries::getPublishedByAlias($alias);

		if (!$series) {
			return $this->notFound($request, $response);
		}
		
		$comic = $series->issueByNumber($comicNumber);
		
		if (!$comic) {
			return $this->notFound($request, $response);
		}
		
		$page = $comic->pageByNumber($pageNumber);
		
		if (!$page) {
			return $this->notFound($request, $response);
		}

		$params = $this->buildParams([
			'game' => $series->game(),
			'global_context' => true,
			'large_image' => $this->linker->abs($page->url),
			'params' => [
				'series' => $series,
				'comic' => $comic,
				'page' => $page,
				'title' => $page->titleName(),
				'comics_title' => $this->comicsTitle,
				'rel_prev' => $page->prev() ? $page->prev()->pageUrl() : null,
				'rel_next' => $page->next() ? $page->next()->pageUrl() : null,
			],
		]);

		return $this->view->render($response, 'main/comics/issue_page.twig', $params);
	}
	
	public function standalonePage($request, $response, $args)
	{
		$alias = $args['alias'];
		$pageNumber = $args['page'];

		$comic = ComicStandalone::getPublishedByAlias($alias);

		if (!$comic) {
			return $this->notFound($request, $response);
		}

		$page = $comic->pageByNumber($pageNumber);

		if (!$page) {
			return $this->notFound($request, $response);
		}

		$params = $this->buildParams([
			'game' => $comic->game(),
			'global_context' => true,
			'large_image' => $this->linker->abs($page->url()),
			'params' => [
				'comic' => $comic,
				'page' => $page,
				'title' => $page->titleName(),
				'comics_title' => $this->comicsTitle,
				'rel_prev' => $page->prev() ? $page->prev()->pageUrl() : null,
				'rel_next' => $page->next() ? $page->next()->pageUrl() : null,
			],
		]);

		return $this->view->render($response, 'main/comics/standalone_page.twig', $params);
	}
}
