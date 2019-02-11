<?php

namespace App\Controllers;

class RecipeController extends BaseController {
	private $recipesTitle;
	private $game;
	
	public function __construct($container) {
		parent::__construct($container);

		$this->recipesTitle = $this->getSettings('recipes.title');

		$gameAlias = $this->getSettings('recipes.game');
		$this->game = $this->db->getGameByAlias($gameAlias);
	}
	
	public function index($request, $response, $args) {
		$skillAlias = $args['skill'];
		$page = $request->getQueryParam('page', 1);
		$query = $request->getQueryParam('q', null);
		$rebuild = $request->getQueryParam('rebuild', false);

		$skill = $this->db->getSkillByAlias($skillAlias);

		$pageSize = $this->getSettings('recipes.page_size');

		$title = $skill
			? $skill['name_ru']
			: $this->recipesTitle;

		if ($skill) {
			$titleEn = $skill['name'];
			$breadcrumbs = [
				[
					'url' => $this->router->pathFor('main.recipes'),
					'text' => $this->recipesTitle,
				]
			];
		}

		// paging
		$count = $this->db->getRecipeCount($skill['id'], $query);
		$url = $this->linker->recipes($skill);

		if ($query) {
			$url .= '?q=' . htmlspecialchars($query);
		}
		
		$paging = $this->builder->buildComplexPaging($url, $count, $page, $pageSize);

		$skillRows = $this->db->getSkills();

		$skills = array_map(function($s) {
			return $this->builder->buildSkill($s);
		}, $skillRows);
		
		$cursorStart = ($page - 1) * $pageSize;
		$rows = $this->db->getRecipes($cursorStart, $pageSize, $skill['id'], $query);

		$recipes = array_map(function($r) {
			return $this->builder->buildRecipe($r, $rebuild === false);
		}, $rows);

		$params = $this->buildParams([
			'game' => $this->game,
			'sidebar' => [ 'stream', 'gallery' ],
			'params' => [
				'disqus_url' => $this->linker->disqusRecipes($skill),
				'disqus_id' => 'recipes' . ($skill ? '_' . $skill['alias'] : ''),
				'base_url' => $this->linker->recipes(),
				'skills' => $skills,
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
		
		$row = $this->db->getRecipe($id);

		if (!$row) {
			return $this->notFound($request, $response);
		}
		
		$recipe = $this->builder->buildRecipe($row, $rebuild);
		
		$title = $recipe['name_ru'];
		if (isset($recipe['name']) && $recipe['name'] != $recipe['name_ru']) {
			$title .= ' (' . $recipe['name'] . ')';
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
				'title' => $title,
			],
		]);

		return $this->view->render($response, 'main/recipes/item.twig', $params);
	}
}
