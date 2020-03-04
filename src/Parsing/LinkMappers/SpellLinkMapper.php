<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Parsing\LinkMappers\Basic\TaggedLinkMapper;
use App\Parsing\LinkMappers\Traits\RecipeLink;
use App\Parsing\LinkMappers\Traits\WowheadLink;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Parsing\SlugChunk;

class SpellLinkMapper extends TaggedLinkMapper
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
        return 'spell';
    }

    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $id = $slugChunk->slug();
        $text = $otherChunks[0] ?? null;

        return
            $this->getRecipeLink($id, $text)
            ??
            $this->renderWowheadLink($this->tag(), $id, $text);
    }

    private function getRecipeLink(string $spellId, ?string $text) : ?string
    {
        if (!is_numeric($spellId)) {
            return null;
        }

        $recipe = $this->recipeRepository->get($spellId);
    
        return $recipe
            ? $this->renderRecipeLink($recipe, $text)
            : null;
    }
}
