<?php

namespace App\Core;

use Plasticode\Core\Renderer as RendererBase;

class Renderer extends RendererBase
{
    protected function articleUrlBare($name, $cat)
    {
        if ($cat) {
            $cat = '/' . $cat;
        }

        return '%article%/' . $name . $cat;
    }

    public function articleUrl($nameRu, $nameEn, $nameEsc, $cat, $catEsc, $style = "nd_article")
    {
        if ($cat) {
            $cat = " ({$cat})";
        }

        $url = $this->articleUrlBare($nameEsc, $catEsc);

        return $this->component('url', [
            'url' => $url,
            'text' => $nameRu,
            'title' => $nameEn . $cat,
            'style' => $style,
        ]);
    }

    public function noArticleUrl($nameRu, $nameEn, $cat = null)
    {
        if ($cat) {
            $cat = " ({$cat})";
        }

        return $this->component('span', [
            'text' => $nameRu,
            'title' => $nameEn . $cat,
            'style' => 'nd_noarticle',
        ]);
    }
    
    public function entityUrl($url, $text, $title = null)
    {
        return $this->component('url', [
            'url' => $url,
            'text' => $text,
            'title' => $title,
            'style' => 'nd_article',
        ]);
    }

    public function recipePageUrl($url, $title, $rel = null, $content = '[~]')
    {
        return $this->component('url', [
            'url' => $url,
            'text' => $content,
            'title' => $title,
            'rel' => $rel,
        ]);
    }
}
