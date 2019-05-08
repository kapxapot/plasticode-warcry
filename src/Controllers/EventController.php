<?php

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Game;

class EventController extends Controller
{
	private $eventsTitle;
	
	public function __construct($container)
	{
		parent::__construct($container);

		$this->eventsTitle = $this->getSettings('events.title');
	}

	public function index($request, $response, $args)
	{
		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => $this->eventsTitle,
				'events' => Event::getGroups(),
				'event_games' => Game::getAll(),
				'event_types' => EventType::getAll(),
			],
		]);
	
		return $this->view->render($response, 'main/events/index.twig', $params);
	}

	public function item($request, $response, $args)
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
        
        $description = $event->parsedDescription();

		$params = $this->buildParams([
			'game' => $event->game(),
			'global_context' => true,
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'large_image' => $description['large_image'],
			'image' => $description['image'],
			'params' => [
				'event' => $event,
				'title' => $event->name,
				'events_title' => $this->eventsTitle,
				'page_description' => $this->makePageDescription($description['text'], 'events.description_limit'),
			],
		]);

		return $this->view->render($response, 'main/events/item.twig', $params);
	}
}
