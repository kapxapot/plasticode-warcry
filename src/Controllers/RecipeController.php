<?php

namespace App\Controllers;

use App\Models\Game;
use App\Models\Recipe;
use App\Models\Skill;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class RecipeController extends Controller
{
    /**
     * Recipes title for views
     *
     * @var string
     */
    private $recipesTitle;

    /**
     * Game
     *
     * @var App\Models\Game
     */
    private $game;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->recipesTitle = $this->settingsProvider->getSettings('recipes.title', 'Recipes');

        $gameAlias = $this->settingsProvider->getSettings('recipes.game');
        $this->game = Game::getPublishedByAlias($gameAlias);
    }
    
    public function index(SlimRequest $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $skillAlias = $args['skill'];
        
        $page = $request->getQueryParam('page', 1);
        $query = $request->getQueryParam('q', null);
        $rebuild = $request->getQueryParam('rebuild', null);

        $skill = Skill::getByAlias($skillAlias);

        $pageSize = $this->settingsProvider->getSettings('recipes.page_size');

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
            $url .= '?q=' . urlencode($query);
        }
        
        $paging = $this->pagination->complex($url, $count, $page, $pageSize);

        $offset = ($page - 1) * $pageSize;
        $recipes = Recipe::getAllFiltered($skillId, $query)
            ->slice($offset, $pageSize)
            ->all();
        
        if ($rebuild !== null) {
            $recipes->apply(
                function ($r) {
                    $r->reset();
                }
            );
        }

        $params = $this->buildParams(
            [
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
            ]
        );

        return $this->render($response, 'main/recipes/index.twig', $params);
    }
    
    public function item(SlimRequest $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];
        
        $rebuild = $request->getQueryParam('rebuild', false);
        
        $recipe = Recipe::get($id);

        if (!$recipe) {
            return $this->notFound($request, $response);
        }
        
        if ($rebuild) {
            $recipe->reset();
        }
        
        $params = $this->buildParams(
            [
                'game' => $this->game,
                'sidebar' => [ 'stream', 'gallery' ],
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
