<?php

namespace App\Config;

use App\Config\Interfaces\RecipeConfigInterface;
use App\Config\Interfaces\SkillConfigInterface;
use App\Config\Interfaces\StreamConfigInterface;
use Plasticode\Config\Config as ConfigBase;

class Config extends ConfigBase implements RecipeConfigInterface, SkillConfigInterface, StreamConfigInterface
{
    public function defaultWoWIcon() : string
    {
        return $this->get('default_wow_icon', 'inv_misc_questionmark');
    }

    public function streamTimeToLive() : int
    {
        return $this->get('streams.ttl', 14);
    }
}
