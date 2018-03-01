<?php

namespace App\Controllers;

use Plasticode\Util\Sort;

class TagController extends BaseController {
	public function item($request, $response, $args) {
		$tag = $args['tag'];

		if (strlen($tag) == 0) {
			return $this->notFound($request, $response);
		}
		
		$parts = $this->builder->buildTagParts($tag);

		$params = $this->buildParams([
			'sidebar' => [ 'stream' ],
			'params' => [
				'tag' => $tag,
				'title' => "Тег «{$tag}»", 
				'parts' => $parts,
			],
		]);
	
		return $this->view->render($response, 'main/tags/item.twig', $params);
	}
}
