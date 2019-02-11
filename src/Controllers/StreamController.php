<?php

namespace App\Controllers;

use App\Jobs\UpdateStreamsJob;
use App\Models\Stream;
use App\Services\StreamStatsService;

class StreamController extends Controller
{
	private $streamsTitle;
	
	public function __construct($container)
	{
		parent::__construct($container);

		$this->streamsTitle = $this->getSettings('streams.title');
	}

	public function index($request, $response, $args)
	{
		$streams = Stream::getAllSorted();
		$groups = Stream::getGroups();

		$params = $this->buildParams([
			'sidebar' => [ 'gallery' ],
			'params' => [
				'title' => $this->streamsTitle,
				'streams' => $streams,
				'groups' => $groups,
			],
		]);
	
		return $this->view->render($response, 'main/streams/index.twig', $params);
	}

	public function item($request, $response, $args)
	{
		$alias = $args['alias'];

		$stream = Stream::getPublishedByAlias($alias);
		
		if (!$stream) {
			return $this->notFound($request, $response);
		}
		
		$statsService = new StreamStatsService();
		
		$params = $this->buildParams([
			'sidebar' => [ 'gallery' ],
			'image' => $stream->remoteLogo,
			'params' => [
				'stream' => $stream,
				'stats' => $statsService->build($stream),
				'title' => $stream->title,
				'streams_title' => $this->streamsTitle,
			],
		]);

		return $this->view->render($response, 'main/streams/item.twig', $params);
	}
	
	public function refresh($request, $response, $args)
	{
		$log = $request->getQueryParam('log', false);
		$notify = $request->getQueryParam('notify', true);
		
		$job = new UpdateStreamsJob($this->container, $notify);

		$params = [ 
			'data' => $job->run(),
			'log' => $log,
		];

		return $this->view->render($response, 'main/streams/refresh.twig', $params);
	}
}
