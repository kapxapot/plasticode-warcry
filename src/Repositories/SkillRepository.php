<?php

namespace App\Repositories;

use App\Collections\SkillCollection;
use App\Models\Skill;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class SkillRepository extends IdiormRepository implements SkillRepositoryInterface
{
    protected string $entityClass = Skill::class;

    public function get(?int $id) : ?Skill
    {
        return $this->getEntity($id);
    }

    public function getAllActive() : SkillCollection
    {
        return SkillCollection::from(
            $this->getActiveQuery()
        );
    }

    public function getByAlias(?string $alias) : ?Skill
    {
        return $this
            ->getActiveQuery()
            ->where('alias', $alias)
            ->one();
    }

    protected function getActiveQuery() : Query
    {
        return $this->query()->where('active', 1);
    }
}
