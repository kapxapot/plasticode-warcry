<?php

namespace App\Repositories\Interfaces;

use App\Models\ForumPost;
use App\Models\ForumTopic;

interface ForumPostRepositoryInterface
{
    function getByForumTopic(ForumTopic $topic) : ?ForumPost;
}
