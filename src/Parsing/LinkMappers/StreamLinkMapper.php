<?php

namespace App\Parsing\LinkMappers;

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
