<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ForumMember extends DbModel
{
    protected static $idField = 'member_id';
    
    // GETTERS - ONE

    public static function getByName($name)
    {
        return self::getByField('name', $name);
    }
    
    // PROPS

    public function pageUrl()
    {
        return self::$linker->forumUser($this->getId());
    }
}
