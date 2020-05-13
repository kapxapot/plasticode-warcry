<?php

namespace App\Services;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Util\Strings;

class TwitterService
{
    private LinkerInterface $linker;

    public function __construct(LinkerInterface $linker)
    {
        $this->linker = $linker;
    }

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
            $message .= PHP_EOL . PHP_EOL . $embedUrl;
        }

        return $message;
    }
}
