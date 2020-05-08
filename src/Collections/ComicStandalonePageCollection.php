<?php

namespace App\Collections;

use App\Models\ComicStandalonePage;

class ComicStandalonePageCollection extends ComicPageBaseCollection
{
    protected string $class = ComicStandalonePage::class;

    public function byNumber(int $number) : ?ComicStandalonePage
    {
        return parent::byNumber($number);
    }
}
