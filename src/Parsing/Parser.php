<?php

namespace App\Parsing;

use App\Models\Recipe;
use Plasticode\Parsing\Parsers\CompositeParser;

class Parser extends CompositeParser
{
    private function renderCustomTag(string $tag, string $id, ?string $content, array $chunks) : ?string
    {
        switch ($tag) {
            case 'item':
                return $this->renderItem($id, $content);

            case 'spell':
                return $this->renderRecipe($id, $content);
        }

        return $this->renderWowheadLink($tag, $id, $content);
    }

    private function renderItem(string $id, ?string $content) : ?string
    {
        $itemLink = $this->renderWowheadLink('item', $id, $content);
        $recipe = $this->renderItemRecipe($id);

        if ($recipe) {
            $itemLink .= '&nbsp;' . $recipe;
        }

        return $itemLink;
    }

    /**
     * Default text for tags.
     * 
     * In most cases it's exactly what's needed.
     *
     * @return null|string
     */
    private function renderWowheadLink(string $tag, string $id, ?string $content) : ?string
    {
        $mappings = [
            'ach' => 'achievement',
            'wowevent' => 'event',
        ];

        $dbTag = $mappings[$tag] ?? $tag;
        $urlChunk = $dbTag . '=' . $id;
        $url = $this->getWebDbLink($urlChunk);

        return $this->renderer->component(
            'url',
            [
                'url' => $url,
                'text' => $content ?? $id,
                'data' => ['wowhead' => $urlChunk],
            ]
        );
    }

    private function renderItemRecipe(string $id) : ?string
    {
        $recipe = Recipe::getByItemId($id);
        
        return $recipe
            ? $this->renderRecipeLink($recipe)
            : null;
    }

    /**
     * If spell is a recipe, link it to our recipe page.
     *
     * @return null|string
     */
    private function renderRecipe(string $id, ?string $content) : ?string
    {
        $recipe = Recipe::get($id);
        
        return $recipe
            ? $this->renderRecipeLink($recipe, $content)
            : null;
    }

    private function renderRecipeLink(Recipe $recipe, ?string $content = null) : ?string
    {
        $title = 'Рецепт: ' . ($content ?? $recipe->nameRu);
        $rel = 'spell=' . $recipe->getId() . '&amp;domain=ru';
        
        $url = $recipe->url();
        $recipeUrl = $this->renderer->recipePageUrl($url, $title, $rel, $content);

        return $recipeUrl;
    }
}
