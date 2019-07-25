<?php

namespace App\Controllers;

use Plasticode\Core\Response;

use App\Services\SearchService;

class SearchController extends Controller
{
    public function search($request, $response, $args)
    {
        $query = $args['query'];
        
        $searchService = new SearchService($this->linker);
        
        $result = $searchService->search($query);
        
        return Response::json($response, $result);
    }
}
