<?php

namespace App\Repositories;

use App\Collections\ForumTagCollection;
use App\Collections\ForumTopicCollection;
use App\Models\ForumTag;
use App\Models\ForumTopic;
use App\Repositories\Interfaces\ForumTagRepositoryInterface;
use Plasticode\Collections\Basic\ScalarCollection;
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

    public function getForumTopicIdsByTag(string $tag) : ScalarCollection
    {
        return $this
            ->getForumTopicTagsByTag($tag)
            ->metaIds();
    }

    public function getForumTopicTagsByTag(string $tag) : ForumTagCollection
    {
        $tag = mb_strtolower($tag);

        return ForumTagCollection::from(
            $this
                ->topicQuery()
                ->whereRaw('(lcase(tag_text) = ?)', [$tag])
        );
    }

    protected function topicQuery() : Query
    {
        return $this
            ->query()
            ->where('tag_meta_app', 'forums')
            ->where('tag_meta_area', 'topics');
    }
}
