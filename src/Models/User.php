<?php

namespace App\Models;

use Plasticode\Models\User as UserBase;

class User extends UserBase
{
    public function forumMember()
    {
        return ForumMember::getByName($this->forumName ?? $this->login);
    }
}
