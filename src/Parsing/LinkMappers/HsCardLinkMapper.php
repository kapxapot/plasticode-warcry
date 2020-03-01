<?php

namespace App\Parsing\LinkMappers;

use App\Parsing\LinkMappers\Basic\TaggedLinkMapper;
use Plasticode\Parsing\LinkMappers\Traits\SimpleMapSlug;
use Plasticode\ViewModels\UrlViewModel;

class HsCardLinkMapper extends TaggedLinkMapper
{
    use SimpleMapSlug;

    public function tag() : string
    {
        return 'card';
    }

    protected function renderSlug(string $slug, string $text) : ?string
    {
        $url = $this->linker->hsCard($slug);

        return $this->renderer->url(
            new UrlViewModel(
                $url,
                $text,
                null,
                'hh-ttp'
            )
        );
    }
}
