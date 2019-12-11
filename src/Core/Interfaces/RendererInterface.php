<?php

namespace App\Core\Interfaces;

use Plasticode\Core\Interfaces\RendererInterface as PlasticodeRendererInterface;

interface RendererInterface extends PlasticodeRendererInterface
{
    public function articleUrl(string $nameRu, string $nameEn, string $nameEsc, ?string $cat, ?string $catEsc, ?string $style = null) : string;

    public function noArticleUrl(string $nameRu, string $nameEn, ?string $cat = null) : string;

    public function entityUrl(string $url, string $text, ?string $title = null) : string;

    public function recipePageUrl(string $url, ?string $title, ?string $rel = null, ?string $content = null) : string;
}
