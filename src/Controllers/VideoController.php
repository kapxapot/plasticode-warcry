<?php

namespace App\Controllers;

use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property VideoRepositoryInterface $videoRepository
 */
class VideoController extends NewsSourceController
{
    /**
     * Videos title for views
     *
     * @var string
     */
    private $videosTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->videosTitle = $this->getSettings('videos.title') ?? 'Videos';
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery', 'news'],
                'params' => [
                    'title' => $this->videosTitle,
                    'videos' => Video::getPublished(),
                ],
            ]
        );
    
        return $this->render($response, 'main/videos/index.twig', $params);
    }

    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];

        $video = $this->videoRepository->getProtected($id);

        if (!$video) {
            return $this->notFound($request, $response);
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
                    'page_description' => $this->makeNewsPageDescription($video, 'videos.description_limit'),
                ],
            ]
        );

        return $this->render($response, 'main/videos/item.twig', $params);
    }
}
