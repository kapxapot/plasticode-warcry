<?php

namespace App\Parsing\LinkMappers\Traits;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use Plasticode\ViewModels\UrlViewModel;

/**
 * @property RendererInterface $renderer
 * @property LinkerInterface $linker
 */
trait WowheadLink
{
    protected $mappings = [
        'ach' => 'achievement',
        'wowevent' => 'event',
    ];

    protected function renderWowheadLink(string $tag, string $id, ?string $text) : ?string
    {
        $wowheadTag = $this->mappings[$tag] ?? $tag;
        $urlChunk = $wowheadTag . '=' . $id;
        $url = $this->linker->wowheadUrlRu($urlChunk);

        return $this->renderer->url(
            new UrlViewModel(
                $url,
                $text ?? $id,
                null,
                null,
                null,
                ['wowhead' => $urlChunk]
            )
        );
    }
}
