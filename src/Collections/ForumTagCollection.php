<?php

namespace App\Collections;

use App\Models\ForumTag;
use Plasticode\TypedCollection;

class ForumTagCollection extends TypedCollection
{
    protected string $class = ForumTag::class;
}
