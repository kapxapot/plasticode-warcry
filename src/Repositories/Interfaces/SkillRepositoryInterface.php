<?php

namespace App\Repositories\Interfaces;

use App\Collections\SkillCollection;
use App\Models\Skill;

interface SkillRepositoryInterface
{
    function get(?int $id) : ?Skill;
    function getAllActive() : SkillCollection;
    function getByAlias(string $alias) : ?Skill;
}
