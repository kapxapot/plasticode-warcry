<?php

namespace App\Core;

use App\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer as RendererBase;

class Renderer extends RendererBase implements RendererInterface
{
    protected function articleUrlBare(string $name, ?string $cat) : string
    {
        if (strlen($cat) > 0) {
            $cat = '/' . $cat;
        }

        return '%article%/' . $name . $cat;
    }

    public function articleUrl(string $text, string $nameEn, string $nameEsc, ?string $cat, ?string $catEsc, ?string $style = null) : string
    {
        if (strlen($cat) > 0) {
            $cat = ' (' . $cat . ')';
        }

        $url = $this->articleUrlBare($nameEsc, $catEsc);

        return $this->component(
            'url',
            [
                'url' => $url,
                'text' => $text,
                'title' => $nameEn . $cat,
                'style' => $style ?? 'entity-url',
            ]
        );
    }

    public function noArticleUrl(string $nameRu, string $nameEn, ?string $cat = null) : string
    {
        if (strlen($cat) > 0) {
            $cat = ' (' . $cat . ')';
        }

        return $this->component(
            'span',
            [
                'text' => $nameRu,
                'title' => $nameEn . $cat,
                'style' => 'no-url',
            ]
        );
    }

    public function recipePageUrl(string $url, ?string $title, ?string $rel = null, ?string $content = null) : string
    {
        return $this->component(
            'url',
            [
                'url' => $url,
                'text' => $content ?? '[~]',
                'title' => $title,
                'rel' => $rel,
            ]
        );
    }
}
