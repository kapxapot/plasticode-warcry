<?php

namespace App\Collections;

use App\Models\ComicPageBase;
use Plasticode\Collections\Basic\DbModelCollection;

abstract class ComicPageBaseCollection extends DbModelCollection
{
    protected string $entity = ComicPageBase::class;
}
