<?php

namespace App\Services;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Contained;
use Plasticode\Util\Strings;

class TwitterService extends Contained
{
    public function buildMessage(NewsSourceInterface $news) : string
    {
        $url = $news->url();
        $url = $this->linker->abs($url);

        $message = $news->displayTitle() . ' ' . $url;
        
        $tags = $news->getTags();

        if (!empty($tags)) {
            $message .= PHP_EOL . PHP_EOL . Strings::hashTags($tags);
        }

        $embedUrl = $news->video();

        if (strlen($embedUrl) > 0) {
            $message .= ' ' . $embedUrl;
        }

        return $message;
    }
}
