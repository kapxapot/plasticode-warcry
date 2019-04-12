<?php

namespace App\Services;

use Plasticode\Collection;

use App\Models\Article;
use App\Models\Event;
use App\Models\ComicIssue;
use App\Models\ComicSeries;
use App\Models\ComicStandalone;
use App\Models\GalleryPicture;
use App\Models\Stream;
use App\Services\GalleryService;
use App\Services\NewsAggregatorService;
use App\Services\StreamService;

class TagPartsProviderService
{
    private $galleryService;
    private $newsAggregatorService;
    private $streamService;

    public function __construct(NewsAggregatorService $newsAggregatorService, StreamService $streamService, GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
        $this->newsAggregatorService = $newsAggregatorService;
        $this->streamService = $streamService;
    }
    
	public function getParts($tag)
	{
	    $picturesQuery = GalleryPicture::getByTag($tag);
	    $pictures = $this->galleryService->getPage($picturesQuery)->all();
	    
		$parts = [];
		
		$groups = [
			[
				'id' => 'news',
				'label' => 'Новости',
				'values' => $this->newsAggregatorService->getByTag($tag),
				'component' => 'news'
			],
			[
				'id' => 'articles',
				'label' => 'Статьи',
				'values' => Article::getByTag($tag)
				    ->orderByDesc('published_at')
				    ->all(),
				'component' => 'articles'
			],
			[
				'id' => 'gallery_pictures',
				'label' => 'Галерея',
				'values' => $pictures,
				'component' => 'gallery_pictures'
			],
			[
				'id' => 'comics',
				'label' => 'Комиксы',
				'values' => Collection::merge(
				    ComicIssue::getByTag($tag)->all(),
				    ComicSeries::getByTag($tag)->all(),
				    ComicStandalone::getByTag($tag)->all()
				),
				'component' => 'comics'
			],
			[
				'id' => 'streams',
				'label' => 'Стримы',
				'values' => $this->streamService->getByTag($tag),
				'component' => 'streams'
			],
			[
				'id' => 'events',
				'label' => 'События',
				'values' => Event::getByTag($tag)
				    ->orderByDesc('starts_at')
				    ->all(),
				'component' => 'events'
			],
		];

		foreach ($groups as $group) {
			if (count($group['values']) > 0) {
				$parts[] = $group;
			}
		}

		return $parts;
	}
}
