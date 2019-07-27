<?php

namespace App\Controllers;

use App\Services\NewsAggregatorService;
use App\Services\StreamService;
use App\Services\TagPartsProviderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TagController extends Controller
{
    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $tag = $args['tag'];

        if (strlen($tag) == 0) {
            return $this->notFound($request, $response);
        }
        
        $newsAggregatorService = new NewsAggregatorService();
        $streamService = new StreamService($this->cases);

        $tagPartsProviderService = new TagPartsProviderService(
            $newsAggregatorService,
            $streamService,
            $this->galleryService
        );
        
        $parts = $tagPartsProviderService->getParts($tag);

        $params = $this->buildParams(
            [
                'sidebar' => [ 'stream', 'gallery' ],
                'params' => [
                    'tag' => $tag,
                    'title' => "Тег «{$tag}»", 
                    'parts' => $parts,
                ],
            ]
        );
    
        return $this->render($response, 'main/tags/item.twig', $params);
    }
}
