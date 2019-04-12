<?php

namespace App\Controllers;

use App\Models\Game;
use App\Models\Recipe;
use App\Models\Skill;

class RecipeController extends Controller {
	private $recipesTitle;
	private $game;

	public function __construct($container) {
		parent::__construct($container);

		$this->recipesTitle = $this->getSettings('recipes.title');

		$gameAlias = $this->getSettings('recipes.game');
		$this->game = Game::getPublishedByAlias($gameAlias);
	}
	
	public function index($request, $response, $args) {
		$skillAlias = $args['skill'];
		
		$page = $request->getQueryParam('page', 1);
		$query = $request->getQueryParam('q', null);
		$rebuild = $request->getQueryParam('rebuild', false);

		$skill = Skill::getByAlias($skillAlias);

		$pageSize = $this->getSettings('recipes.page_size');

		$title = $skill
			? $skill['name_ru']
			: $this->recipesTitle;

		if ($skill) {
		    $skillId = $skill->getId();
		    
			$titleEn = $skill['name'];
			$breadcrumbs = [
				[
					'url' => $this->router->pathFor('main.recipes'),
					'text' => $this->recipesTitle,
				]
			];
		}

		// paging
		$count = Recipe::getAllFiltered($skillId, $query)->count();
		$url = $this->linker->recipes($skill);

		if ($query) {
			$url .= '?q=' . htmlspecialchars($query);
		}
		
		$paging = $this->pagination->complex($url, $count, $page, $pageSize);

		$offset = ($page - 1) * $pageSize;
		$recipes = Recipe::getAllFiltered($skillId, $query)
		    ->slice($offset, $pageSize)
		    ->all();
		
		if ($rebuild) {
		    $recipes->apply(function ($r) {
		        $r->reset();
		    });
		}

		$params = $this->buildParams([
			'game' => $this->game,
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'disqus_url' => $this->linker->disqusRecipes($skill),
				'disqus_id' => 'recipes' . ($skill ? '_' . $skill['alias'] : ''),
				'base_url' => $this->linker->recipes(),
				'skills' => Skill::getActive()->all(),
				'skill' => $skill,
				'recipes' => $recipes,
				'title' => $title,
				'title_en' => $titleEn,
				'breadcrumbs' => $breadcrumbs,
				'query' => $query,
				'paging' => $paging,
				'page_size' => $pageSize,
			],
		]);

		return $this->view->render($response, 'main/recipes/index.twig', $params);
	}
	
	public function item($request, $response, $args) {
		$id = $args['id'];
		
		$rebuild = $request->getQueryParam('rebuild', false);
		
		$recipe = Recipe::get($id);

		if (!$recipe) {
			return $this->notFound($request, $response);
		}
		
		if ($rebuild) {
		    $recipe->reset();
		}
		
		$params = $this->buildParams([
			'game' => $this->game,
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'disqus_url' => $this->linker->disqusRecipe($id),
				'disqus_id' => 'recipe' . $id,
				'recipes_title' => $this->recipesTitle,
				//'breadcrumbs' => $breadcrumbs,
				'recipe' => $recipe,
				'title' => $recipe->title(),
			],
		]);

		return $this->view->render($response, 'main/recipes/item.twig', $params);
	}
}
