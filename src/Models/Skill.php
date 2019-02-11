<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class Skill extends DbModel
{
    // getters - many

	public static function getAllActive() {
		return self::getAll(function ($q) {
		    return $q->where('active', 1);    
		});
	}
    
    // getters - one

	public static function getByAlias($alias) {
		return self::getAllActive()
		    ->where('alias', $alias)
		    ->first();
	}

    // props
    
    public function displayIcon()
    {
        return $this->icon ?? self::getSettings('recipes.default_icon');
    }

    public function iconUrl()
    {
        return self::$linker->wowheadIcon($this->displayIcon());
    }
}
