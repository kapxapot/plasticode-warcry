<?php

namespace App\Controllers;

use App\Models\Interfaces\NewsSourceInterface;

abstract class NewsSourceController extends Controller
{
    public function makeNewsPageDescription(
        NewsSourceInterface $news,
        string $limitVar
    ) : string
    {
        return $this->makePageDescription($news->fullText(), $limitVar);
    }
}
