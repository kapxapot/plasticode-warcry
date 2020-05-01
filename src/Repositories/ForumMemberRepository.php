<?php

namespace App\Repositories;

use App\Models\ForumMember;
use App\Repositories\Interfaces\ForumMemberRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ForumMemberRepository extends IdiormRepository implements ForumMemberRepositoryInterface
{
    protected string $entityClass = ForumMember::class;

    public function get(?int $id) : ?ForumMember
    {
        return $this->getEntity($id);
    }

    public function getByName(string $name) : ?ForumMember
    {
        return $this
            ->query()
            ->where('name', $name)
            ->one();
    }
}
