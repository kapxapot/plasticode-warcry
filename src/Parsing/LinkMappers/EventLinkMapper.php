<?php

namespace App\Parsing\LinkMappers;

use App\Parsing\LinkMappers\Basic\TaggedEntityLinkMapper;

class EventLinkMapper extends TaggedEntityLinkMapper
{
    protected function entity() : string
    {
        return 'event';
    }

    protected function baseUrl() : string
    {
        return $this->linker->event();
    }
}
