<?php

namespace App\Generators;

use Respect\Validation\Validator as v;

use Plasticode\Generators\EntityGenerator;

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
		$parts = [];
		$cur = $item;
		
		while ($cur != null) {
		    $parts[] = $cur['name_ru'];
		    
	        $cur = $cur['terminal']
		        ? null
		        : $this->db->getRegion($cur['parent_id']);
		}

		$item['select_title'] = implode(' » ', array_reverse($parts));

		return $item;
	}
}
