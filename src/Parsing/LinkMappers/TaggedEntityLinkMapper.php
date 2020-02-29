<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\TaggedEntityLinkMapper as TaggedEntityLinkMapperBase;

abstract class TaggedEntityLinkMapper extends TaggedEntityLinkMapperBase
{
    /** @var LinkerInterface */
    protected $linker;

    public function __construct(RendererInterface $renderer, LinkerInterface $linker)
    {
        parent::__construct($renderer, $linker);
    }
}
