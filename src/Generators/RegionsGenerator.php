<?php

namespace App\Generators;

use Respect\Validation\Validator as v;

use Plasticode\Generators\EntityGenerator;

use App\Models\Region;

class RegionsGenerator extends EntityGenerator
{
    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['parent_id'] = v::nonRecursiveParent($this->entity, $id);
        
        return $rules;
    }
    
    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $parts = [];
        
        $cur = Region::get($item[$this->idField]);
        
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
