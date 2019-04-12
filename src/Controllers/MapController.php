<?php

namespace App\Controllers;

use App\Models\Article;

class MapController extends Controller
{
	public function index($request, $response, $args)
	{
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => $this->getSettings('map.title'),
				'items' => Article::publishedOrphans(),
				'no_disqus' => 1,
				'no_social' => 1,
			],
		]);

		return $this->view->render($response, 'main/map/index.twig', $params);
	}
}
