<?php

namespace App\Collections;

use App\Models\ForumTag;
use Plasticode\Collections\Basic\DbModelCollection;

class ForumTagCollection extends DbModelCollection
{
    protected string $class = ForumTag::class;
}
