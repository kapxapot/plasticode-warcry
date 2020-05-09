<?php

namespace App\Collections;

use App\Models\ComicPage;

abstract class ComicPageCollection extends NumberedCollection
{
    protected string $class = ComicPage::class;

    public function byNumber(int $number) : ?ComicPage
    {
        return parent::byNumber($number);
    }

    public function prev(int $number) : ?ComicPage
    {
        return parent::prev($number);
    }

    public function next(int $number) : ?ComicPage
    {
        return parent::next($number);
    }
}
