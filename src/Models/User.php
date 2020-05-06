<?php

namespace App\Models;

use Plasticode\Models\User as BaseUser;

/**
 * @property string|null $forumName
 * @method ForumMember|null forumMember()
 * @method static withForumMember(ForumMember|callable|null $forumMember)
 */
class User extends BaseUser
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'forumMember',
        ];
    }
}
