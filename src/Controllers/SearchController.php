<?php

namespace App\Controllers;

use App\Services\SearchService;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Response;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property TagRepositoryInterface $tagRepository
 * @property LinkerInterface $linker
 */
class SearchController extends Controller
{
    public function search(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $query = $args['query'];
        
        $searchService = new SearchService(
            $this->tagRepository,
            $this->linker
        );
        
        $result = $searchService->search($query);
        
        return Response::json($response, $result);
    }
}
