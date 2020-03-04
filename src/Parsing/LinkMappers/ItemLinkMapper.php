<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Parsing\LinkMappers\Basic\TaggedLinkMapper;
use App\Parsing\LinkMappers\Traits\RecipeLink;
use App\Parsing\LinkMappers\Traits\WowheadLink;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Parsing\SlugChunk;

class ItemLinkMapper extends TaggedLinkMapper
{
    use WowheadLink, RecipeLink;

    /** @var RecipeRepositoryInterface */
    private $recipeRepository;

    public function __construct(
        RecipeRepositoryInterface $recipeRepository,
        RendererInterface $renderer,
        LinkerInterface $linker
    )
    {
        parent::__construct($renderer, $linker);

        $this->recipeRepository = $recipeRepository;
    }

    public function tag() : string
    {
        return 'item';
    }

    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $id = $slugChunk->slug();
        $text = $otherChunks[0] ?? $id;

        $link = $this->renderWowheadLink($this->tag(), $id, $text);
        $recipeLink = $this->getRecipeLink($id);

        if ($recipeLink) {
            $link .= ' ' . $recipeLink;
        }

        return $link;
    }

    private function getRecipeLink(string $itemId) : ?string
    {
        if (!is_numeric($itemId)) {
            return null;
        }

        $recipe = $this->recipeRepository->getByItemId($itemId);
    
        return $recipe
            ? $this->renderRecipeLink($recipe)
            : null;
    }
}
