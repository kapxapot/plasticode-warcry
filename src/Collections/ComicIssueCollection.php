<?php

namespace App\Collections;

use App\Models\ComicIssue;
use Plasticode\Collections\Basic\DbModelCollection;

class ComicIssueCollection extends DbModelCollection
{
    protected string $class = ComicIssue::class;
}
