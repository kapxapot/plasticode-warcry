<?php

namespace App\Handlers;

use Plasticode\Contained;

class NotFoundHandler extends Contained {
	public function __invoke($request, $response) {
		$game = $this->db->getDefaultGame();
		$games = $this->db->getGames();
		
		return $this->view->render($response, 'main/generic.twig', [
			'menu' => $this->builder->buildMenuByGame($game),
			'game' => $this->builder->buildGame($game),
			'games' => $this->builder->buildGames($games),
			'text' => 'Страница не найдена или перемещена.',
			'title' => 'Ошибка 404',
			'no_disqus' => 1,
			'no_social' => 1,
		])->withStatus(404);
	}
}
