<?php

namespace App\Controllers;

use App\Jobs\UpdateStreamsJob;
use App\Models\Stream;
use App\Services\StreamService;
use App\Services\StreamStatsService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StreamController extends Controller
{
    /**
     * Streams title for views
     *
     * @var string
     */
    private $streamsTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->streamsTitle = $this->getSettings('streams.title');
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $streamService = new StreamService($this->cases);
        
        $streams = $streamService->getAllSorted();
        $groups = $streamService->getGroups();

        $params = $this->buildParams(
            [
                'sidebar' => [ 'gallery' ],
                'params' => [
                    'title' => $this->streamsTitle,
                    'streams' => $streams,
                    'groups' => $groups,
                ],
            ]
        );
    
        return $this->render($response, 'main/streams/index.twig', $params);
    }

    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $alias = $args['alias'];

        $stream = Stream::getPublishedByAlias($alias);
        
        if (!$stream) {
            return $this->notFound($request, $response);
        }
        
        $statsService = new StreamStatsService();
        
        $params = $this->buildParams(
            [
                'sidebar' => [ 'gallery' ],
                'image' => $stream->remoteLogo,
                'params' => [
                    'stream' => $stream,
                    'stats' => $statsService->build($stream),
                    'title' => $stream->title,
                    'streams_title' => $this->streamsTitle,
                ],
            ]
        );

        try {
            $rendered = $this->view->render($response, 'main/streams/item.twig', $params);
        } catch (\Exception $ex) {
            $this->logger->debug($ex->getMessage(), $stream);
            return $this->notFound($request, $response);
        }
        
        return $rendered;
    }
    
    public function refresh(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $log = $request->getQueryParam('log', false);
        $notify = $request->getQueryParam('notify', false);
        
        $job = new UpdateStreamsJob($this->container, $notify);

        $params = [ 
            'data' => $job->run(),
            'log' => $log,
        ];

        return $this->render($response, 'main/streams/refresh.twig', $params);
    }
}
