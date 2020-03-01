<?php

namespace App\Parsing\LinkMappers;

use App\Parsing\LinkMappers\Basic\TaggedEntityLinkMapper;

class StreamLinkMapper extends TaggedEntityLinkMapper
{
    protected function entity() : string
    {
        return 'stream';
    }

    protected function baseUrl() : string
    {
        return $this->linker->stream();
    }
}
