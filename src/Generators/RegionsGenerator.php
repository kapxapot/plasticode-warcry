<?php

namespace App\Generators;

use Respect\Validation\Validator as v;

use Plasticode\Generators\EntityGenerator;

use App\Models\Region;

class RegionsGenerator extends EntityGenerator
{
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
		$rules['parent_id'] = v::nonRecursiveParent($this->entity, $id);
		
		return $rules;
	}
	
	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);
	    
		$parts = [];
		
		$cur = Region::get($item['id']);
		
		while ($cur != null) {
		    $parts[] = $cur->nameRu;
		    
	        $cur = $cur->terminal
		        ? null
		        : $cur->parent();
		}

		$item['select_title'] = implode(' » ', array_reverse($parts));

		return $item;
	}
}
