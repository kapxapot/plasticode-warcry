<?php

namespace App\Repositories\Interfaces;

use App\Models\ForumMember;

interface ForumMemberRepositoryInterface
{
    function get(?int $id) : ?ForumMember;
    function getByName(string $name) : ?ForumMember;
}
