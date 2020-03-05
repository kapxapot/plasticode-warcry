<?php

namespace App\Parsing\LinkMappers\Traits;

use App\Core\Interfaces\RendererInterface;
use App\Models\Recipe;

/**
 * @property RendererInterface $renderer
 */
trait RecipeLink
{
    protected function renderRecipeLink(Recipe $recipe, ?string $text = null) : ?string
    {
        $title = 'Рецепт: ' . ($text ?? $recipe->nameRu);
        $rel = 'spell=' . $recipe->getId() . '&domain=ru';
        
        $url = $recipe->url();
        $recipeUrl = $this->renderer->recipePageUrl($url, $title, $rel, $text);

        return $recipeUrl;
    }
}
