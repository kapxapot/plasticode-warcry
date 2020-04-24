<?php

namespace App\Config;

use App\Models\Article;
use App\Models\Comic;
use App\Models\ComicSeries;
use App\Models\ComicStandalone;
use App\Models\Event;
use App\Models\GalleryPicture;
use App\Models\News;
use App\Models\Stream;
use App\Models\Video;
use Plasticode\Config\Interfaces\TagsConfigInterface;

class TagsConfig implements TagsConfigInterface
{
    public function getTab(string $class): ?string
    {
        $map = [
            Article::class => 'articles',
            News::class => 'news',
            Event::class => 'events',
            GalleryPicture::class => 'gallery',
            ComicSeries::class => 'comics',
            Comic::class => 'comics',
            ComicStandalone::class => 'comics',
            Video::class => 'videos',
            Stream::class => 'streams',
        ];

        return $map[$class] ?? null;
    }
}
