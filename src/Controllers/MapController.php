<?php

namespace App\Controllers;

class MapController extends Controller {
	public function index($request, $response, $args) {
		$rootId = $this->getSettings('articles.root_id');
		$map = $this->builder->getSubArticles($rootId, true);
	    
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => $this->getSettings('map.title'),
				'items' => $map,
				'no_disqus' => 1,
				'no_social' => 1,
			],
		]);

		return $this->view->render($response, 'main/map/index.twig', $params);
	}
}
