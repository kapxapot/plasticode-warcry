<?php

namespace App\Controllers;

use App\Jobs\UpdateStreamsJob;
use App\Models\Stream;
use App\Services\StreamService;
use App\Services\StreamStatService;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\External\Twitch;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class StreamController extends Controller
{
    /** @var CacheInterface */
    private $cache;

    /** @var Twitch */
    private $twitch;

    /** @var StreamService */
    private $streamService;

    /** @var StreamStatService */
    private $streamStatService;

    /**
     * Streams title for views
     *
     * @var string
     */
    private $streamsTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->cache = $container->cache;
        $this->twitch = $container->twitch;
        $this->streamService = $container->streamService;
        $this->streamStatService = $container->streamStatService;

        $this->streamsTitle = $this->settingsProvider->getSettings('streams.title', 'Streams');
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $streams = $this->streamService->getAllSorted();
        $groups = $this->streamService->getGroups();

        $params = $this->buildParams(
            [
                'sidebar' => ['gallery'],
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
        
        $params = $this->buildParams(
            [
                'sidebar' => ['gallery'],
                'image' => $stream->remoteLogo,
                'params' => [
                    'stream' => $stream,
                    'stats' => $this->streamStatService->build($stream),
                    'title' => $stream->title,
                    'streams_title' => $this->streamsTitle,
                ],
            ]
        );

        try {
            $rendered = $this->view->render($response, 'main/streams/item.twig', $params);
        } catch (\Exception $ex) {
            $this->logger->debug($ex->getMessage(), $stream->toArray());
            return $this->notFound($request, $response);
        }
        
        return $rendered;
    }
    
    public function refresh(SlimRequest $request, ResponseInterface $response) : ResponseInterface
    {
        $log = $request->getQueryParam('log', false);
        $notify = $request->getQueryParam('notify', false);
        
        $job = new UpdateStreamsJob(
            $this->settingsProvider,
            $this->cache,
            $this->linker,
            $this->twitch,
            $this->logger,
            $notify
        );

        $params = [ 
            'data' => $job->run(),
            'log' => $log,
        ];

        return $this->render($response, 'main/streams/refresh.twig', $params);
    }
}
