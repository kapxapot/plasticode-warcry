<?php

namespace App\Parsing\LinkMappers;

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
