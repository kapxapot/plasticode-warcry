<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;

class Recipe extends DbModel
{
    protected $built = false;
    protected $forceBuild = false;

    private $baseReagents;
    private $link;
	private $reagentsList;
    private $requiredSkills;
    
    // getters many
    
	public static function getAllByItemId($itemId) : Collection
	{
		return self::query()
			->where('creates_id', $itemId)
			->whereGt('creates_min', 0)
			->all();
	}
	
	public static function getAllFiltered($skillId = null, $searchQuery = null) : Query
	{
	    $query = self::query();

		if ($skillId) {
			$query = $query->where('skill_id', $skillId);
		}

		if ($searchQuery) {
		    $query = $query->search($searchQuery, '(name like ? or name_ru like ?)', 2);
		}
		
		return $query
			->orderByAsc('learnedat')
			->thenByAsc('lvl_orange')
			->thenByAsc('lvl_yellow')
			->thenByAsc('lvl_green')
			->thenByAsc('lvl_gray')
			->thenByAsc('name_ru');
	}

    // getters - one
    
	public static function getByName($name)
	{
		return self::query()
			->whereRaw('(name like ? or name_ru like ?)', [ $name, $name ])
			->one();
	}
    
	public static function getByItemId($itemId)
	{
		return self::getAllByItemId($itemId)->first();
	}

	// props
	
	public function skill()
	{
	    return Skill::get($this->skillId);
	}

	public function title()
	{
		$title = $this->nameRu;
		
		if ($this->name && $this->name != $this->nameRu) {
			$title .= ' (' . $this->name . ')';
		}
		
		return $title;
	}
	
	public function levels()
	{
		return [
			'orange' => $this->lvlOrange,
			'yellow' => $this->lvlYellow,
			'green' => $this->lvlGreen,
			'gray' => $this->lvlGray,
		];
	}

    public function sources()
    {
		$srcIds = explode(',', $this->source);
		
		return array_map(function ($srcId) {
			$src = RecipeSource::get($srcId);
			return $src ? $src->nameRu : $srcId;
		}, $srcIds);
    }
    
    public function invQuality()
    {
        return 8 - $this->quality;
    }
    
    public function url()
    {
		return self::$linker->recipe($this->getId());
    }
    
    public function baseReagents()
    {
        $this->buildIfNeeded();
        
        return $this->baseReagents;
    }
    
    public function link()
    {
        $this->buildIfNeeded();
        
        return $this->link;
    }
    
    public function reagentsList()
    {
        $this->buildIfNeeded();
        
        return $this->reagentsList;
    }
    
    public function requiredSkills()
    {
        $this->buildIfNeeded();
        
        return $this->requiredSkills;
    }

    // funcs

    public function reset()
    {
        $this->built = false;
        $this->forceBuild = true;
    }
    
    protected function buildIfNeeded()
    {
        if (!$this->built || $this->forceBuild) {
            $this->build($this->forceBuild);
        }
    }
    
	private function extractReagents()
	{
		$reagents = [];
		
		if (strlen($this->reagents) > 0) {
			$chunks = explode(',', $this->reagents);
			
			foreach ($chunks as $chunk) {
				list($id, $count) = explode('x', $chunk);
				$reagents[$id] = $count;
			}
		}
		
		return $reagents;
	}
	
	public function getBaseReagentsAndSkills(&$baseReagents = [], &$requiredSkills = [])
	{
		foreach ($this->reagentsList as $reagent) {
			$skillId = $this->skillId;

			if (!isset($requiredSkills[$skillId])) {
				$requiredSkills[$skillId] = [
					'skill' => $this->skill,
					'max' => $this->learnedat,
				];
			}
			else {
				$requiredSkills[$skillId]['max'] = max($requiredSkills[$skillId]['max'], $recipe->learnedat);
			}
			
			if (isset($reagent['recipe'])) {
				$reagent['recipe']->getBaseReagentsAndSkills($baseReagents, $requiredSkills);
			}
			else {
				$id = $reagent['item_id'];
			
				if (!isset($baseReagents[$id])) {
					$baseReagents[$id] = $reagent;
				}
				else {
					$baseReagents[$id]['total_min'] += $reagent['total_min'];
					$baseReagents[$id]['total_max'] += $reagent['total_max'];
				}
			}
		}
	}
	
	public function addNodeIds($label = '1')
	{
	    //var_dump($label);
		$this->nodeId = $label;

		$count = 1;
		foreach ($this->reagentsList as &$reagent) {
			if (isset($reagent['recipe'])) {
				$reagent['recipe']->addNodeIds($label . '_' . $count++);
			}
		}
	}
	
	private function addTotals($countMin = 1, $countMax = 1)
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
		
		return $recipe;
	}
	
	private static function getSpellIcon($id)
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
					    ->where(function ($r) {
						    return !preg_match('/^Transmute/', $r->name);
					    });

					foreach ($srcRecipes as $srcRecipe) {
						$srcRegs = $srcRecipe->extractReagents();
						
						// no recursion pls -__-
						$badReagents = array_filter(array_keys($srcRegs), function($srcRegId) use ($trunk) {
							return in_array($srcRegId, $trunk);
						});
						
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
			}
			else {
				$link =	[
					'icon' => self::getSpellIcon($this->getId()),
					'spell_id' => $this->getId(),
				];
			}

			//$this->iconCache = json_encode($link);
			//$this->save();
		//}

		$this->link = self::buildRecipeLink($link);

		$this->reagentsList = array_map(function($r) {
			return self::buildRecipeLink($r);
		}, $reagents);

		$this->built = true;
		$this->forceBuild = false;

		if ($topLevel) {
    		$this->addNodeIds();
	        $this->addTotals();
	        
		    $baseReagents = [];
		    
			$this->getBaseReagentsAndSkills($baseReagents, $this->requiredSkills);
			
			$this->baseReagents = array_map(function($r) {
				return self::buildRecipeLink($r);
			}, array_values($baseReagents));
		}
	}
	
	private static function buildRecipeLink($link)
	{
		$link['icon_url'] = self::$linker->wowheadIcon($link['icon'] ?? self::getSettings('recipes.default_icon'));

		if (isset($link['item_id'])) {
			$link['item_url'] = self::$linker->wowheadItemRu($link['item_id']);
		}
		
		if (isset($link['spell_id'])) {
			$link['spell_url'] = self::$linker->wowheadSpellRu($link['spell_id']);
		}
		
		$link['url'] = $link['item_url'] ?? $link['spell_url'];

		return $link;
	}
}
