<?php

namespace App\Parsing\LinkMappers;

class VideoLinkMapper extends TaggedEntityLinkMapper
{
    protected function entity() : string
    {
        return 'video';
    }

    protected function baseUrl() : string
    {
        return $this->linker->video();
    }
}
