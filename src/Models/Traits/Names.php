<?php

namespace App\Models\Traits;

trait Names
{
    public function name()
    {
        return $this->nameRu ?? $this->nameEn;
    }
    
    public function subName()
    {
		return ($this->nameRu && $this->nameRu != $this->nameEn)
		    ? $this->nameEn
		    : null;
    }
    
    public function fullName()
    {
        $name = $this->name();
        
		if ($this->subName()) {
			$name .= ' (' . $this->subName() . ')';
		}
	    
        return $name;
    }
}
