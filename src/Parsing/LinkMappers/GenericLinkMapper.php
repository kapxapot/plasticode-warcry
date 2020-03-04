<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Parsing\LinkMappers\Traits\WowheadLink;
use Plasticode\Parsing\LinkMappers\Basic\SlugLinkMapper;
use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

class GenericLinkMapper extends SlugLinkMapper
{
    use WowheadLink;

    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    public function __construct(RendererInterface $renderer, LinkerInterface $linker)
    {
        $this->renderer = $renderer;
        $this->linker = $linker;
    }

    protected function validateSlugChunk(SlugChunk $slugChunk) : void
    {
        Assert::notEmpty($slugChunk->tag());
    }

    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $id = $slugChunk->slug();
        $text = $otherChunks[0] ?? $id;

        return $this->renderWowheadLink($slugChunk->tag(), $id, $text);
    }
}
