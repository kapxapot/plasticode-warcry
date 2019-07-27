<?php

namespace App\Controllers;

use App\Models\Video;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VideoController extends Controller
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

        $this->videosTitle = $this->getSettings('videos.title');
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $params = $this->buildParams(
            [
                'sidebar' => [ 'stream', 'gallery', 'news' ],
                'params' => [
                    'title' => $this->videosTitle,
                    'videos' => Video::getPublished()->all(),
                ],
            ]
        );
    
        return $this->render($response, 'main/videos/index.twig', $params);
    }

    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];

        $video = Video::getProtected()->find($id);

        if (!$video) {
            return $this->notFound($request, $response);
        }
        
        $description = $video->parsedDescription();

        $params = $this->buildParams(
            [
                'game' => $video->game(),
                'global_context' => true,
                'sidebar' => [ 'stream', 'gallery', 'news' ],
                'params' => [
                    'video' => $video,
                    'title' => $video->name,
                    'videos_title' => $this->videosTitle,
                    'page_description' => $this->makePageDescription(
                        $description,
                        'videos.description_limit'
                    ),
                ],
            ]
        );

        return $this->render($response, 'main/videos/item.twig', $params);
    }
}
