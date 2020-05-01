<?php

namespace App\Repositories;

use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Repositories\Interfaces\ForumPostRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ForumPostRepository extends IdiormRepository implements ForumPostRepositoryInterface
{
    protected string $entityClass = ForumPost::class;

    public function getByForumTopic(ForumTopic $topic) : ?ForumPost
    {
        return $this
            ->query()
            ->where('topic_id', $topic->getId())
            ->where('new_topic', 1)
            ->one();
    }
}
