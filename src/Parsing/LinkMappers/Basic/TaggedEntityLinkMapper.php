<?php

namespace App\Parsing\LinkMappers\Basic;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\Basic\TaggedEntityLinkMapper as TaggedEntityLinkMapperBase;

abstract class TaggedEntityLinkMapper extends TaggedEntityLinkMapperBase
{
    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    public function __construct(RendererInterface $renderer, LinkerInterface $linker)
    {
        parent::__construct($renderer, $linker);
    }
}
