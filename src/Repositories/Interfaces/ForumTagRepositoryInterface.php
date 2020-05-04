<?php

namespace App\Repositories\Interfaces;

use App\Collections\ForumTagCollection;
use App\Models\ForumTopic;
use Plasticode\Collections\Basic\ScalarCollection;

interface ForumTagRepositoryInterface
{
    function getAllByForumTopic(ForumTopic $topic) : ForumTagCollection;
    function getForumTopicIdsByTag(string $tag) : ScalarCollection;
}
