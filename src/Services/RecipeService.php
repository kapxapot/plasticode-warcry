<?php

namespace App\Services;

use App\Config\Interfaces\RecipeConfigInterface;
use App\Core\Interfaces\LinkerInterface;

class RecipeService
{
    private RecipeConfigInterface $config;
    private LinkerInterface $linker;

    public function __construct(
        RecipeConfigInterface $config,
        LinkerInterface $linker
    )
    {
        $this->config = $config;
        $this->linker = $linker;
    }

    private function getBaseReagentsAndSkills(
        array &$baseReagents = [],
        array &$requiredSkills = []
    )
    {
        foreach ($this->reagentsList as $reagent) {
            $skillId = $this->skillId;

            if (!isset($requiredSkills[$skillId])) {
                $requiredSkills[$skillId] = [
                    'skill' => $this->skill,
                    'max' => $this->learnedat,
                ];
            } else {
                $requiredSkills[$skillId]['max'] = max(
                    $requiredSkills[$skillId]['max'],
                    $this->learnedat
                );
            }

            if (isset($reagent['recipe'])) {
                $reagent['recipe']->getBaseReagentsAndSkills($baseReagents, $requiredSkills);
            } else {
                $id = $reagent['item_id'];

                if (!isset($baseReagents[$id])) {
                    $baseReagents[$id] = $reagent;
                } else {
                    $baseReagents[$id]['total_min'] += $reagent['total_min'];
                    $baseReagents[$id]['total_max'] += $reagent['total_max'];
                }
            }
        }
    }

    public function addNodeIds(string $label = '1') : void
    {
        $this->nodeId = $label;

        $count = 1;

        foreach ($this->reagentsList as &$reagent) {
            if (isset($reagent['recipe'])) {
                $reagent['recipe']->addNodeIds($label . '_' . $count++);
            }
        }
    }

    private function addTotals($countMin = 1, $countMax = 1) : self
    {
        $createsMin = $this->createsMin;
        $createsMax = $this->createsMax;

        $neededMin = ($createsMax > 0) ? ceil($countMin / $createsMax) : 0;
        $neededMax = ($createsMin > 0) ? ceil($countMax / $createsMin) : 0;

        $this->totalMin = $neededMin;
        $this->totalMax = $neededMax;

        foreach ($this->reagentsList as &$reagent) {
            $count = $reagent['count'];

            $totalMin = ($neededMin > 0) ? $neededMin * $count : $count;
            $totalMax = ($neededMax > 0) ? $neededMax * $count : $count;

            $reagent['total_min'] = $totalMin;
            $reagent['total_max'] = $totalMax;

            if (isset($reagent['recipe'])) {
                $reagent['recipe']->addTotals($totalMin, $totalMax);
            }
        }

        return $this;
    }

    private static function getSpellIcon(int $id) : ?string
    {
        $icon = SpellIcon::get($id);
        return ($icon != null) ? $icon->icon : null;
    }

    public function build($rebuild = false, &$requiredSkills = [], $trunk = [])
    {
        //var_dump($this . ' build!');

        $topLevel = empty($trunk);

        // на всякий -__-
        if (count($trunk) > 20) {
            return;
        }

        $trunk[] = $this->createsId;

        // reagents
        /*if (!$rebuild && strlen($recipe->reagentCache) > 0) {
            $reagents = json_decode($recipe->reagentCache, true);
        }
        else {*/
            $reagents = [];

            $extRegs = $this->extractReagents();

            foreach ($extRegs as $id => $count) {
                $item = Item::getSafe($id);

                $reagent = [
                    'icon' => ($item != null) ? $item['icon'] : null,
                    'item_id' => $id,
                    'count' => $count,
                    'item' => $item,
                ];

                // going deeper?
                $foundRecipe = null;

                if (!in_array($id, $trunk)) {
                    $srcRecipes = $item
                        ->recipes()
                        // skipping transmutes
                        ->where(
                            fn ($r) => !preg_match('/^Transmute/', $r->name)
                        );

                    foreach ($srcRecipes as $srcRecipe) {
                        $srcRegs = $srcRecipe->extractReagents();

                        // no recursion pls -__-
                        $badReagents = array_filter(
                            array_keys($srcRegs),
                            fn ($srcRegId) => in_array($srcRegId, $trunk)
                        );

                        if (empty($badReagents)) {
                            $srcRecipe->build($rebuild, $requiredSkills, $trunk);
                            $foundRecipe = $srcRecipe;
                            break;
                        }
                    }
                }

                $reagent['recipe'] = $foundRecipe;

                $reagents[] = $reagent;
            }

            //$this->reagentCache = json_encode($reagents);
            //$this->save();
        //}

        // link
        /*if (!$rebuild && strlen($recipe->iconCache) > 0) {
            $link = json_decode($recipe->iconCache, true);
        }
        else {*/
            if ($this->createsId != 0) {
                $item = Item::getSafe($this->createsId);

                $link = [
                    'icon' => ($item != null) ? $item->icon : null,
                    'item_id' => $this->createsId,
                    'count' => '!!createsMin!!',
                    'max_count' => '!!createsMax!!',
                    'spell_id' => $this->getId(),
                ];
            } else {
                $link = [
                    'icon' => self::getSpellIcon($this->getId()),
                    'spell_id' => $this->getId(),
                ];
            }

            //$this->iconCache = json_encode($link);
            //$this->save();
        //}

        $this->link = $this->buildRecipeLink($link);

        $this->reagentsList = array_map(
            fn ($r) => $this->buildRecipeLink($r),
            $reagents
        );

        $this->built = true;
        $this->forceBuild = false;

        if ($topLevel) {
            $this->addNodeIds();
            $this->addTotals();

            $baseReagents = [];

            $this->getBaseReagentsAndSkills(
                $baseReagents,
                $this->requiredSkills
            );

            $this->baseReagents = array_map(
                fn ($r) => $this->buildRecipeLink($r),
                array_values($baseReagents)
            );
        }
    }

    private function buildRecipeLink(array $link) : array
    {
        $icon =
            $link['icon']
            ?? $this->config->defaultWoWIcon();

        $link['icon_url'] = $this->linker->wowheadIcon($icon);

        if (isset($link['item_id'])) {
            $link['item_url'] =
                $this->linker->wowheadItemRu($link['item_id']);
        }

        if (isset($link['spell_id'])) {
            $link['spell_url'] =
                $this->linker->wowheadSpellRu($link['spell_id']);
        }

        $link['url'] = $link['item_url'] ?? $link['spell_url'];

        return $link;
    }
}
