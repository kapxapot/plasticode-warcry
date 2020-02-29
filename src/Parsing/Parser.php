<?php

namespace App\Parsing;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Models\GalleryPicture;
use App\Models\Location;
use App\Models\Recipe;
use Plasticode\Collection;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Util\Numbers;

class Parser extends CompositeParser
{
    /** @var RendererInterface */
    private $renderer;

    /** @var LinkerInterface */
    private $linker;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    public function __construct(
        RendererInterface $renderer,
        LinkerInterface $linker,
        SettingsProviderInterface $settingsProvider
    )
    {
        parent::__construct();

        $this->renderer = $renderer;
        $this->linker = $linker;
        $this->settingsProvider = $settingsProvider;
    }

    protected function getWebDbLink(string $appendix) : string
    {
        return $this->settingsProvider->getSettings('webdb_ru_link') . $appendix;
    }

    private function renderCustomTag(string $tag, string $id, ?string $content, array $chunks) : ?string
    {
        switch ($tag) {
            case 'item':
                return $this->renderItem($id, $content);

            case 'spell':
                return $this->renderRecipe($id, $content);

            case 'coords':
                return $this->renderCoords($id, $chunks);

            case 'card':
                return $this->renderHearthstoneCard($id, $content);

            case 'gallery':
                return $this->renderGallery($id, $chunks);
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

    private function renderCoords(string $id, array $chunks) : ?string
    {
        if (count($chunks) <= 2) {
            return null;
        }

        $x = $chunks[1];
        $y = $chunks[2];
        
        $coordsText = '[' . round($x) . ',&nbsp;' . round($y) . ']';

        if (!is_numeric($id)) {
            $location = Location::getByName($id);
            
            if (!$location) {
                return null;
            }

            $id = $location->getId();
        }

        if ($id <= 0) {
            return null;
        }

        $coords = '';
        
        $x = Numbers::parseFloat($x);
        $y = Numbers::parseFloat($y);
        
        if ($x > 0 && $y > 0) {
            $coords = ':' . ($x * 10) . ($y * 10);
        }
        
        $url = $this->getWebDbLink('maps?data=' . $id . $coords);
        
        return $this->renderer->component(
            'url',
            [
                'url' => $url,
                'text' => $coordsText,
            ]
        );
    }

    private function renderHearthstoneCard(string $id, ?string $content) : ?string
    {
        $url = $this->linker->hsCard($id);

        return $this->renderer->component(
            'url',
            [
                'url' => $url,
                'text' => $content ?? $id,
                'style' => 'hh-ttp',
            ]
        );
    }

    private function renderGallery(string $id, array $chunks) : ?string
    {
        $pictures = Collection::makeEmpty();
        
        $ids = explode(',', $id);

        $chunksCount = count($chunks);

        if ($chunksCount > 1) {
            for ($i = 1; $i < $chunksCount; $i++) {
                $chunk = $chunks[$i];
                
                if (is_numeric($chunk) && $chunk > 0) {
                    $maxPictures = $chunk;
                } elseif (mb_strtolower($chunk) == 'grid') {
                    $gridMode = true;
                }
            }
        }

        foreach ($ids as $id) {
            if (is_numeric($id)) {
                $pic = GalleryPicture::get($id);
                
                if ($pic) {
                    $pictures = $pictures->add($pic);
                }
            } else {
                $limit = $maxPictures
                    ?? $this->settingsProvider->getSettings('gallery.inline_limit');
                
                $query = GalleryPicture::getByTag($id);
                $pictures = $this->galleryService->getPage($query, 1, $limit)->all();
                $inlineTag = $id;

                break;
            }
        }

        if ($pictures->empty()) {
            return null;
        }

        $tagLink = $inlineTag
            ? $this->linker->tag($inlineTag, 'gallery_pictures')
            : null;

        return $this->renderer->component(
            'gallery_inline',
            [
                'pictures' => $pictures,
                'tag_link' => $tagLink,
                'grid_mode' => $gridMode === true,
            ]
        );
    }
}
