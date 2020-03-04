<?php

namespace App\Core;

use App\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer as RendererBase;
use Plasticode\ViewModels\UrlViewModel;

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

        return $this->url(
            new UrlViewModel(
                $url,
                $text,
                $nameEn . $cat,
                $style ?? 'entity-url'
            )
        );
    }

    public function noArticleUrl(string $nameRu, string $nameEn, ?string $cat = null) : string
    {
        if (strlen($cat) > 0) {
            $cat = ' (' . $cat . ')';
        }

        return $this->noUrl($nameRu, $nameEn . $cat);
    }

    public function recipePageUrl(string $url, ?string $title, ?string $rel = null, ?string $text = null) : string
    {
        return $this->url(
            new UrlViewModel(
                $url,
                $text ?? '[~]',
                $title,
                null,
                $rel
            )
        );
    }
}
