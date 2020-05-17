<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class EventController extends NewsSourceController
{
    private EventRepositoryInterface $eventRepository;
    private NotFoundHandler $notFoundHandler;

    /**
     * Events title for views.
     */
    private string $eventsTitle;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->eventRepository = $container->eventRepository;
        $this->notFoundHandler = $container->notFoundHandler;

        $this->eventsTitle = $this->getSettings('events.title', 'Events');
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $events = $this->eventRepository->getAllOrderedByStart();

        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery'],
                'params' => [
                    'title' => $this->eventsTitle,
                    'events' => $events->groups(),
                ],
            ]
        );

        return $this->render($response, 'main/events/index.twig', $params);
    }

    public function item(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
     ) : ResponseInterface
    {
        $id = $args['id'];
        
        $rebuild = $request->getQueryParam('rebuild', null);

        $event = $this->eventRepository->getProtected($id);

        if (!$event) {
            return ($this->notFoundHandler)($request, $response);
        }

        if ($rebuild !== null) {
            // Todo: reset event description
            // Currently, there's no caching
        }

        $params = $this->buildParams(
            [
                'game' => $event->game(),
                'global_context' => true,
                'sidebar' => ['stream', 'gallery', 'news'],
                'large_image' => $event->largeImage(),
                'image' => $event->image(),
                'params' => [
                    'event' => $event,
                    'title' => $event->name,
                    'events_title' => $this->eventsTitle,
                    'page_description' => $this->makeNewsPageDescription(
                        $event,
                        'events.description_limit'
                    ),
                ],
            ]
        );

        return $this->render($response, 'main/events/item.twig', $params);
    }
}
