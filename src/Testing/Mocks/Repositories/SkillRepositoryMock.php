<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\SkillCollection;
use App\Models\Skill;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class SkillRepositoryMock implements SkillRepositoryInterface
{
    private SkillCollection $skills;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->skills = SkillCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?Skill
    {
        return $this->skills->first('id', $id);
    }

    public function getAllActive() : SkillCollection
    {
        return $this->skills->where('active', 1);
    }

    public function getByAlias(?string $alias) : ?Skill
    {
        return $this->skills->first('alias', $alias);
    }
}
