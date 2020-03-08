<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ForumMember extends DbModel
{
    protected static $idField = 'member_id';
    
    // GETTERS - ONE

    public static function getByName($name)
    {
        return self::query()
            ->where('name', $name)
            ->one();
    }
    
    // PROPS

    public function pageUrl()
    {
        return self::$container->linker->forumUser($this->getId());
    }
}
