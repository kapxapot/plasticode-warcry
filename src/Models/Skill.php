<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;

class Skill extends DbModel
{
    // queries

	public static function getActive() : Query
	{
		return self::query()
		    ->where('active', 1);    
	}
    
    // getters - one

	public static function getByAlias($alias)
	{
		return self::getActive()
		    ->where('alias', $alias)
		    ->one();
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
