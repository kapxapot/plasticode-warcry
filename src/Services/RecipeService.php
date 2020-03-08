<?php

namespace App\Services;

use App\Config\Interfaces\RecipeConfigInterface;
use App\Core\Interfaces\LinkerInterface;

class RecipeService
{
    /** @var RecipeConfigInterface */
    private $config;

    /** @var LinkerInterface */
    private $linker;

    public function __construct(
        RecipeConfigInterface $config,
        LinkerInterface $linker
    )
    {
        $this->config = $config;
        $this->linker = $linker;
    }
    
    private function buildRecipeLink(array $link) : array
    {
        $icon =
            $link['icon']
            ??
            $this->config->defaultWoWIcon();

        $link['icon_url'] = $this->linker->wowheadIcon($icon);

        if (isset($link['item_id'])) {
            $link['item_url'] = $this->linker->wowheadItemRu($link['item_id']);
        }
        
        if (isset($link['spell_id'])) {
            $link['spell_url'] = $this->linker->wowheadSpellRu($link['spell_id']);
        }
        
        $link['url'] = $link['item_url'] ?? $link['spell_url'];

        return $link;
    }
}
