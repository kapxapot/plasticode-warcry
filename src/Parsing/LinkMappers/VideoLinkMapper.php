<?php

namespace App\Parsing\LinkMappers;

class VideoLinkMapper extends TaggedEntityLinkMapper
{
    protected $entity = 'video';

    protected function baseUrl() : string
    {
        return $this->linker->video();
    }
}
