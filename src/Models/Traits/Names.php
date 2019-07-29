<?php

namespace App\Models\Traits;

trait Names
{
    public function name() : string
    {
        return $this->nameRu ?? $this->nameEn;
    }
    
    public function subName() : ?string
    {
        return ($this->nameRu && $this->nameRu != $this->nameEn)
            ? $this->nameEn
            : null;
    }
    
    public function fullName() : string
    {
        $name = $this->name();
        
        if ($this->subName()) {
            $name .= ' (' . $this->subName() . ')';
        }
        
        return $name;
    }
}
