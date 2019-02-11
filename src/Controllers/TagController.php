<?php

namespace App\Controllers;

use Plasticode\Util\Sort;

use App\Services\NewsAggregatorService;
use App\Services\TagPartsProviderService;

class TagController extends Controller
{
	public function item($request, $response, $args)
	{
		$tag = $args['tag'];

		if (strlen($tag) == 0) {
			return $this->notFound($request, $response);
		}
		
		$newsAggregatorService = new NewsAggregatorService;
		$tagPartsProviderService = new TagPartsProviderService($newsAggregatorService);
		
		$parts = $tagPartsProviderService->getParts($tag);

		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'tag' => $tag,
				'title' => "Тег «{$tag}»", 
				'parts' => $parts,
			],
		]);
	
		return $this->view->render($response, 'main/tags/item.twig', $params);
	}
}
