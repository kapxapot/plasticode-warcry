<?php

namespace App\Controllers;

use App\Factories\UpdateStreamsJobFactory;
use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\StreamRepositoryInterface;
use App\Services\StreamService;
use App\Services\StreamStatService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class StreamController extends Controller
{
    private StreamRepositoryInterface $streamRepository;

    private StreamService $streamService;
    private StreamStatService $streamStatService;

    private NotFoundHandler $notFoundHandler;

    private UpdateStreamsJobFactory $updateStreamsJobFactory;

    /**
     * Streams title for views
     */
    private string $streamsTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->streamRepository = $container->streamRepository;

        $this->streamService = $container->streamService;
        $this->streamStatService = $container->streamStatService;

        $this->notFoundHandler = $container->notFoundHandler;

        $this->updateStreamsJobFactory = $container->updateStreamsJobFactory;

        $this->streamsTitle = $this->getSettings('streams.title', 'Streams');
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
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

    public function item(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $alias = $args['alias'];

        $stream = $this->streamRepository->getPublishedByAlias($alias);

        if (is_null($stream)) {
            return ($this->notFoundHandler)($request, $response);
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

        $rendered = null;

        try {
            $rendered = $this->render(
                $response,
                'main/streams/item.twig',
                $params
            );
        } catch (\Exception $ex) {
            $this->logger->debug($ex->getMessage(), $stream->toArray());
            return ($this->notFoundHandler)($request, $response);
        }

        return $rendered;
    }

    public function refresh(
        SlimRequest $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $log = $request->getQueryParam('log', false);
        $notify = $request->getQueryParam('notify', false);

        $job = $this->updateStreamsJobFactory->make($notify);

        $params = [ 
            'data' => $job->run(),
            'log' => $log,
        ];

        return $this->render($response, 'main/streams/refresh.twig', $params);
    }
}
