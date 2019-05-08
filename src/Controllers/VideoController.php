<?php

namespace App\Controllers;

use App\Models\Video;

class VideoController extends Controller
{
	private $videosTitle;
	
	public function __construct($container)
	{
		parent::__construct($container);

		$this->videosTitle = $this->getSettings('videos.title');
	}

	public function index($request, $response, $args)
	{
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'params' => [
				'title' => $this->videosTitle,
				'videos' => Video::getPublished()->all(),
			],
		]);
	
		return $this->view->render($response, 'main/videos/index.twig', $params);
	}

	public function item($request, $response, $args)
	{
		$id = $args['id'];

		$video = Video::getProtected()->find($id);

		if (!$video) {
			return $this->notFound($request, $response);
		}
		
        $description = $video->parsedDescription();

		$params = $this->buildParams([
			'game' => $video->game(),
			'global_context' => true,
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'params' => [
				'video' => $video,
				'title' => $video->name,
				'videos_title' => $this->videosTitle,
				'page_description' => $this->makePageDescription($description, 'videos.description_limit'),
			],
		]);

		return $this->view->render($response, 'main/videos/item.twig', $params);
	}
}
