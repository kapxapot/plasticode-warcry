<?php

namespace App\Parsing;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Models\Article;
use App\Models\GalleryPicture;
use App\Models\Location;
use App\Models\Recipe;
use Plasticode\Collection;
use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Parsing\CompositeParser;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Models\Tag;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Numbers;
use Plasticode\Util\Strings;

class Parser extends CompositeParser
{
    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    /** @var SettingsProviderInterface */
    protected $settingsProvider;

    public function __construct(ParsingConfigInterface $config, RendererInterface $renderer, LinkerInterface $linker, SettingsProviderInterface $settingsProvider)
    {
        parent::__construct($config, $renderer, $linker);

        $this->renderer = $renderer;
        $this->linker = $linker;
        $this->settingsProvider = $settingsProvider;
    }

    protected function getWebDbLink(string $appendix) : string
    {
        return $this->settingsProvider->getSettings('webdb_ru_link') . $appendix;
    }

    protected function parseMore(?string $text) : ?string
    {
        return preg_replace_callback(
            '/\[\[(.*)\]\]/U',
            function ($matches) {
                $original = $matches[0];
                $match = $matches[1];
                
                $parsed = $this->parseDoubleBracketsMatch($match);

                return $parsed ?? $original;
            },
            $text
        );
    }
    
    protected function parseDoubleBracketsMatch(?string $match) : ?string
    {
        if (strlen($match) == 0) {
            return null;
        }
        
        $text = null;
        $chunks = preg_split('/\|/', $match);

        // looking for ":" in first chunk
        $tagChunk = $chunks[0];
        $tagParts = preg_split('/:/', $tagChunk, null, PREG_SPLIT_NO_EMPTY);
        $tag = $tagParts[0];
        
        // one tag part = article
        if (count($tagParts) == 1) {
            return $this->renderArticle($tagChunk, $chunks);
        }

        // many tag parts
        // pattern: [[tag:id|content]]
        // e.g.: [[npc:27412|Слинкин Демогном]]
        $id = $tagParts[1];
        $content = $chunks[1] ?? null;
        $text = $this->renderCustomTag($tag, $id, $content, $chunks);
        
        return strlen($text) > 0
            ? $text
            : null;
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

            case 'news':
            case 'event':
            case 'stream':
            case 'video':
                return $this->renderEntity($tag, $id, $content);

            case 'tag':
                return $this->renderTag($id, $content);
            
            case 'gallery':
                return $this->renderGallery($id, $chunks);
        }

        return $this->renderWowheadLink($tag, $id, $content);
    }

    private function renderArticle(string $id, array $chunks) : ?string
    {
        $chunksCount = count($chunks);

        $cat = '';
        $name = $chunks[$chunksCount - 1];

        if ($chunksCount > 2) {
            $cat = $chunks[1];
        }

        $idEsc = Strings::fromSpaces($id);
        $catEsc = Strings::fromSpaces($cat);

        $article = Article::getByNameOrAlias($id, $cat);

        $text = null;

        if ($article && $article->isPublished()) {
            $text = $this->renderer->articleUrl($name, $id, $idEsc, $cat, $catEsc);
        }

        // if such tag exists, render as tag
        if (!$text && Tag::exists($id)) {
            $text = $this->renderTag($id, $name);
        }

        return $text ?? $this->renderer->noArticleUrl($name, $id, $cat);
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

    private function renderTag(string $id, ?string $content) : ?string
    {
        $id = Strings::fromSpaces($id, '+');
        return $this->renderEntity('tag', $id, $content);
    }

    private function renderEntity(string $tag, string $id, string $content = null) : ?string
    {
        return $this->renderer->entityUrl(
            '%' . $tag . '%/' . $id,
            $content ?? $id
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

    protected function renderBBContainer(array $node) : string
    {
        switch ($node['tag']) {
            case 'bluepost':
                return $this->renderBBNode(
                    'quote',
                    $node,
                    function ($content, $attrs) {
                        $data = $this->mapQuoteBB($content, $attrs);
                        $data = $this->enrichBluepostData($data);
                        
                        return $data;
                    }
                );
                
            default:
                return parent::renderBBContainer($node);
        }
    }
    
    protected function enrichBluepostData(array $data) : array
    {
        $data['author'] = $data['author'] ?? 'Blizzard';
        $data['style'] = 'quote--bluepost';
        
        return $data;
    }
    
    protected function getBBContainerTags() : array
    {
        return array_merge(
            parent::getBBContainerTags(),
            ['bluepost']
        );
    }

    public function renderLinks(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $text = $context->text;

        $text = str_replace('%article%/', $this->linker->article(), $text);
        $text = str_replace('%news%/', $this->linker->news(), $text);
        $text = str_replace('%event%/', $this->linker->event(), $text);
        $text = str_replace('%stream%/', $this->linker->stream(), $text);
        $text = str_replace('%video%/', $this->linker->video(), $text);
        $text = str_replace('%tag%/', $this->linker->tag(), $text);

        $context->text = $text;

        return $context;
    }
}
