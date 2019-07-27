<?php

namespace App\Controllers;

use App\Services\SearchService;
use Plasticode\Core\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SearchController extends Controller
{
    public function search(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $query = $args['query'];
        
        $searchService = new SearchService($this->linker);
        
        $result = $searchService->search($query);
        
        return Response::json($response, $result);
    }
}
