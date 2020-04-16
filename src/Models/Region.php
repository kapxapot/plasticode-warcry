<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class Region extends DbModel
{
    // PROPS
    
    public function parent()
    {
        return $this->parentId
            ? Region::get($this->parentId)
            : null;
    }
    
    public function displayName()
    {
        $ru = [ $this->nameRu ];
        $en = [ $this->nameEn ];

        if ($this->parent() && $this->terminal == 0) {
            $ru[] = $this->parent()->nameRu;
            $en[] = $this->parent()->nameEn;
        }
        
        $ruStr = implode(', ', array_filter($ru, 'strlen'));
        $enStr = implode(', ', array_filter($en, 'strlen'));
        
        return $ruStr . ($enStr ? " ({$enStr})" : '');
    }
}
