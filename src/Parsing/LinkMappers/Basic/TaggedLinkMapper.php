<?php

namespace App\Parsing\LinkMappers\Basic;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\Basic\TaggedLinkMapper as TaggedLinkMapperBase;

abstract class TaggedLinkMapper extends TaggedLinkMapperBase
{
    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    public function __construct(RendererInterface $renderer, LinkerInterface $linker)
    {
        parent::__construct();

        $this->renderer = $renderer;
        $this->linker = $linker;
    }
}
