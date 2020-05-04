<?php

namespace App\Repositories;

use App\Collections\ForumCollection;
use App\Models\Forum;
use App\Models\Game;
use App\Repositories\Interfaces\ForumRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ForumRepository extends IdiormRepository implements ForumRepositoryInterface
{
    protected string $entityClass = Forum::class;

    public function get(?int $id) : ?Forum
    {
        return $this->getEntity($id);
    }

    public function getParent(Forum $forum) : ?Forum
    {
        return $forum->parentId > 0
            ? $this->get($forum->parentId)
            : null;
    }

    public function getAll() : ForumCollection
    {
        return ForumCollection::from(
            parent::getAll()
        );
    }

    public function getAllByGame(Game $game) : ForumCollection
    {
        return $this->getAll()->byGame($game);
    }
}
