<?php

namespace App\Services;

use App\Config\Interfaces\SkillConfigInterface;

class SkillService
{
    /** @var SkillConfigInterface */
    private $config;

    public function __construct(SkillConfigInterface $config)
    {
        $this->config = $config;
    }
}
