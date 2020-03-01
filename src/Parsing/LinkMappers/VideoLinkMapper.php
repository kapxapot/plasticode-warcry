<?php

namespace App\Parsing\LinkMappers;

use App\Parsing\LinkMappers\Basic\TaggedEntityLinkMapper;

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
