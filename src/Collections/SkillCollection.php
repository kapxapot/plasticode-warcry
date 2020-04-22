<?php

namespace App\Collections;

use App\Models\Skill;
use Plasticode\TypedCollection;

class SkillCollection extends TypedCollection
{
    protected string $class = Skill::class;
}
