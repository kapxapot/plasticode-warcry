<?php

namespace App\Collections;

use App\Models\ForumTag;
use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Collections\Basic\ScalarCollection;

class ForumTagCollection extends DbModelCollection
{
    protected string $class = ForumTag::class;

    public function tagTexts() : ScalarCollection
    {
        return $this->scalarize(
            fn (ForumTag $t) => $t->tagText
        );
    }

    public function metaIds() : ScalarCollection
    {
        return $this->scalarize(
            fn (ForumTag $t) => $t->tagMetaId
        );
    }
}
