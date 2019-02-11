<?php

namespace App\Controllers;

use Plasticode\Util\Sort;

class EventController extends BaseController
{
	private $eventsTitle;
	
	public function __construct($container)
	{
		parent::__construct($container);

		$this->eventsTitle = $this->getSettings('events.title');
	}

	public function index($request, $response, $args)
	{
		$rows = $this->db->getEvents();
		$events = $this->builder->buildEvents($rows);

		$params = $this->buildParams([
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'title' => $this->eventsTitle,
				'events' => $events,
				'event_games' => $this->db->getGames(),
				'event_types' => $this->db->getEventTypes(),
			],
		]);
	
		return $this->view->render($response, 'main/events/index.twig', $params);
	}

	public function item($request, $response, $args)
	{
		$id = $args['id'];
		$rebuild = $request->getQueryParam('rebuild', false);

		$row = $this->db->getEvent($id);

		if (!$row) {
			return $this->notFound($request, $response);
		}

		$event = $this->builder->buildEvent($row, $rebuild);

		$params = $this->buildParams([
			'game' => $event['game'],
			'global_context' => true,
			'sidebar' => [ 'stream', 'gallery', 'news' ],
			'large_image' => $event['parsed']['large_image'],
			'image' => $event['parsed']['image'],
			'params' => [
				'event' => $event,
				'title' => $event['name'],
				'events_title' => $this->eventsTitle,
			],
		]);

		return $this->view->render($response, 'main/events/item.twig', $params);
	}
}
