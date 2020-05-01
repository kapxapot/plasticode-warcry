<?php

namespace App\Repositories;

use App\Collections\ForumTagCollection;
use App\Models\ForumTag;
use App\Models\ForumTopic;
use App\Repositories\Interfaces\ForumTagRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class ForumTagRepository extends IdiormRepository implements ForumTagRepositoryInterface
{
    protected string $entityClass = ForumTag::class;

    public function getAllByForumTopic(ForumTopic $topic) : ForumTagCollection
    {
        return ForumTagCollection::from(
            $this
                ->topicQuery()
                ->where('tag_meta_id', $topic->getId())
        );
    }

    public function getForumTopicIdsByTag(string $tag) : Collection
    {
        $tag = mb_strtolower($tag);

        return $this
            ->topicQuery()
            ->whereRaw('(lcase(tag_text) = ?)', [$tag])
            ->all()
            ->extract('tag_meta_id');
    }

    protected function topicQuery() : Query
    {
        return $this
            ->query()
            ->where('tag_meta_app', 'forums')
            ->where('tag_meta_area', 'topics');
    }
}
