<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Models\Game;
use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use Plasticode\Core\Pagination;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class RecipeController extends Controller
{
    private RecipeRepositoryInterface $recipeRepository;
    private SkillRepositoryInterface $skillRepository;

    private Pagination $pagination;
    private NotFoundHandler $notFoundHandler;

    /**
     * Recipes title for views
     */
    private string $recipesTitle;

    private Game $game;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->recipeRepository = $container->recipeRepository;
        $this->skillRepository = $container->skillRepository;

        $this->pagination = $container->pagination;
        $this->notFoundHandler = $container->notFoundHandler;

        $this->recipesTitle = $this->getSettings('recipes.title', 'Recipes');

        $gameAlias = $this->getSettings('recipes.game');
        $this->game = $this->gameRepository->getPublishedByAlias($gameAlias);
    }

    public function index(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $skillAlias = $args['skill'];

        $page = $request->getQueryParam('page', 1);
        $query = $request->getQueryParam('q', null);
        $rebuild = $request->getQueryParam('rebuild', null);

        $skill = $this->skillRepository->getByAlias($skillAlias);

        $pageSize = $this->getSettings('recipes.page_size');

        $title = $skill
            ? $skill['name_ru']
            : $this->recipesTitle;

        $skillId = null;
        $breadcrumbs = null;

        if ($skill) {
            $skillId = $skill->getId();
            $breadcrumbs = [
                [
                    'url' => $this->router->pathFor('main.recipes'),
                    'text' => $this->recipesTitle,
                ]
            ];
        }

        $count = $this
            ->recipeRepository
            ->getFilteredCount($skillId, $query);

        $url = $this->linker->recipes($skill);

        if ($query) {
            $url .= '?q=' . urlencode($query);
        }

        $paging = $this->pagination->complex($url, $count, $page, $pageSize);

        $offset = ($page - 1) * $pageSize;

        $recipes = $this
            ->recipeRepository
            ->getFilteredPage(
                $skillId,
                $query,
                $offset,
                $pageSize
            );

        if ($rebuild !== null) {
            $recipes->apply(
                fn (Recipe $r) => $r->reset()
            );
        }

        $params = $this->buildParams(
            [
                'game' => $this->game,
                'sidebar' => ['stream', 'gallery'],
                'params' => [
                    'disqus_url' => $this->linker->disqusRecipes($skill),
                    'disqus_id' => 'recipes' . ($skill ? '_' . $skill->alias : ''),
                    'base_url' => $this->linker->recipes(),
                    'skills' => $this->skillRepository->getAllActive(),
                    'skill' => $skill,
                    'recipes' => $recipes,
                    'title' => $title,
                    'title_en' => $skill ? $skill->name : null,
                    'breadcrumbs' => $breadcrumbs,
                    'query' => $query,
                    'paging' => $paging,
                    'page_size' => $pageSize,
                ],
            ]
        );

        return $this->render($response, 'main/recipes/index.twig', $params);
    }

    public function item(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $rebuild = $request->getQueryParam('rebuild', false);

        $recipe = $this->recipeRepository->get($id);

        if (!$recipe) {
            return ($this->notFoundHandler)($request, $response);
        }

        if ($rebuild) {
            $recipe->reset();
        }

        $params = $this->buildParams(
            [
                'game' => $this->game,
                'sidebar' => ['stream', 'gallery'],
                'params' => [
                    'disqus_url' => $this->linker->disqusRecipe($id),
                    'disqus_id' => 'recipe' . $id,
                    'recipes_title' => $this->recipesTitle,
                    'recipe' => $recipe,
                    'title' => $recipe->title(),
                ],
            ]
        );

        return $this->render($response, 'main/recipes/item.twig', $params);
    }
}
