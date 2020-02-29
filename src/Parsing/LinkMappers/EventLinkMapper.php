<?php

namespace App\Parsing\LinkMappers;

class EventLinkMapper extends TaggedEntityLinkMapper
{
    protected $entity = 'event';

    protected function baseUrl() : string
    {
        return $this->linker->event();
    }
}
