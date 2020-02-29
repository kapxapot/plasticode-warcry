<?php

namespace App\Parsing\LinkMappers;

class StreamLinkMapper extends TaggedEntityLinkMapper
{
    protected $entity = 'stream';

    protected function baseUrl() : string
    {
        return $this->linker->stream();
    }
}
