<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VideoController extends NewsSourceController
{
    private VideoRepositoryInterface $videoRepository;
    private NotFoundHandler $notFoundHandler;

    /**
     * Videos title for views.
     */
    private string $videosTitle;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->videoRepository = $container->videoRepository;
        $this->notFoundHandler = $container->notFoundHandler;

        $this->videosTitle = $this->getSettings('videos.title', 'Videos');
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery', 'news'],
                'params' => [
                    'title' => $this->videosTitle,
                    'videos' => $this->videoRepository->getAllPublished(),
                ],
            ]
        );

        return $this->render($response, 'main/videos/index.twig', $params);
    }

    public function item(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $video = $this->videoRepository->getProtected($id);

        if (!$video) {
            return ($this->notFoundHandler)($request, $response);
        }

        $params = $this->buildParams(
            [
                'game' => $video->game(),
                'global_context' => true,
                'sidebar' => ['stream', 'gallery', 'news'],
                'params' => [
                    'video' => $video,
                    'title' => $video->name,
                    'videos_title' => $this->videosTitle,
                    'page_description' => $this->makeNewsPageDescription(
                        $video,
                        'videos.description_limit'
                    ),
                ],
            ]
        );

        return $this->render($response, 'main/videos/item.twig', $params);
    }
}
