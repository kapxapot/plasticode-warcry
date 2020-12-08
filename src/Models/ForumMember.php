<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property string $name
 * @method string pageUrl()
 * @method static withPageUrl(string|callable $pageUrl)
 */
class ForumMember extends DbModel
{
    protected static string $idField = 'member_id';

    protected function requiredWiths() : array
    {
        return ['pageUrl'];
    }
}
