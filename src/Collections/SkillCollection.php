<?php

namespace App\Collections;

use App\Models\Skill;
use Plasticode\Collections\Basic\DbModelCollection;

class SkillCollection extends DbModelCollection
{
    protected string $class = Skill::class;
}
