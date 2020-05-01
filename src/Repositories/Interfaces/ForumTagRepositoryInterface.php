<?php

namespace App\Repositories\Interfaces;

use App\Collections\ForumTagCollection;
use App\Models\ForumTopic;
use Plasticode\Collection;

interface ForumTagRepositoryInterface
{
    function getAllByForumTopic(ForumTopic $topic) : ForumTagCollection;
    function getForumTopicIdsByTag(string $tag) : Collection;
}
