<?php

namespace App\Controllers;

use Plasticode\Core\Core;

use App\Services\SearchService;

class SearchController extends Controller
{
	public function search($request, $response, $args)
	{
	    $query = $args['query'];
	    
	    $searchService = new SearchService($this->linker);
	    
	    $result = $searchService->search($query);
	    
		return Core::json($response, $result);
	}
}
