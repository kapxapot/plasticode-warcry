<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property string $tagMetaApp
 * @property string $tagMetaArea
 * @property integer $tagMetaId
 * @property string|null $tagText
 */
class ForumTag extends DbModel
{
    protected static string $idField = 'tag_id';
}
