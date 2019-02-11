<?php

namespace App\Controllers;

class MapController extends BaseController {
	public function index($request, $response, $args) {
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => $this->getSettings('map.title'),
				'items' => $this->builder->buildMap(),
				'no_disqus' => 1,
				'no_social' => 1,
			],
		]);

		return $this->view->render($response, 'main/map/index.twig', $params);
	}
}
