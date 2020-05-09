<?php

namespace App\Models\Traits;

use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @property string|null $tags
 */
trait ComicCommon
{
    use Description;
    use FullPublished;
    use PageUrl;
    use Stamps;
    use Tagged;

    /**
     * @return string[]
     */
    protected function comicCommonProperties() : array
    {
        return [
            $this->pageUrlPropertyName,
            $this->parsedDescriptionPropertyName,
            $this->tagLinksPropertyName,
        ];
    }
}
