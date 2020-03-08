<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;

class Skill extends DbModel
{
    private $defaultIcon;

    public function withDefaultIcon(string $icon) : self
    {
        $this->defaultIcon = $icon;
        return $this;
    }
    
    public static function getActive() : Query
    {
        return self::query()
            ->where('active', 1);
    }

    public static function getByAlias($alias)
    {
        return self::getActive()
            ->where('alias', $alias)
            ->one();
    }

    public function displayIcon()
    {
        return $this->icon ?? $this->defaultIcon;
    }

    public function iconUrl()
    {
        return self::$container->linker->wowheadIcon($this->displayIcon());
    }
}
