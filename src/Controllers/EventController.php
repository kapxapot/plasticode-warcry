<?php

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Game;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EventController extends Controller
{
    /**
     * Events title for views
     *
     * @var string
     */
    private $eventsTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->eventsTitle = $this->getSettings('events.title');
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $params = $this->buildParams(
            [
                'sidebar' => [ 'stream', 'gallery' ],
                'params' => [
                    'title' => $this->eventsTitle,
                    'events' => Event::getGroups(),
                    'event_games' => Game::getAll(),
                    'event_types' => EventType::getAll(),
                ],
            ]
        );
    
        return $this->render($response, 'main/events/index.twig', $params);
    }

    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];
        
        $rebuild = $request->getQueryParam('rebuild', null);

        $event = Event::getProtected()->find($id);

        if (!$event) {
            return $this->notFound($request, $response);
        }

        if ($rebuild !== null) {
            $event->resetDescription();
        }

        $params = $this->buildParams(
            [
                'game' => $event->game(),
                'global_context' => true,
                'sidebar' => [ 'stream', 'gallery', 'news' ],
                'large_image' => $event->largeImage(),
                'image' => $event->image(),
                'params' => [
                    'event' => $event,
                    'title' => $event->name,
                    'events_title' => $this->eventsTitle,
                    'page_description' => $this->makePageDescription($event->shortText(), 'events.description_limit'),
                ],
            ]
        );

        return $this->render($response, 'main/events/item.twig', $params);
    }
}
