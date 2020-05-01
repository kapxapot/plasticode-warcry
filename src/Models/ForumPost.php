<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property integer $newTopic
 * @property string|null $post
 * @property integer $topicId
 */
class ForumPost extends DbModel
{
    protected static string $idField = 'pid';
}
