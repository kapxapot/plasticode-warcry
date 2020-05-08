<?php

namespace App\Collections;

use App\Models\ComicPage;

class ComicPageCollection extends ComicPageBaseCollection
{
    protected string $class = ComicPage::class;

    public function byNumber(int $number) : ?ComicPage
    {
        return parent::byNumber($number);
    }
}
