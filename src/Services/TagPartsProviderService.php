<?php

namespace App\Services;

use Plasticode\Collection;
use Plasticode\Util\Sort;

use App\Models\Article;
use App\Models\Event;
use App\Models\ComicIssue;
use App\Models\ComicSeries;
use App\Models\ComicStandalone;
use App\Models\GalleryPicture;
use App\Models\Stream;
use App\Services\NewsAggregatorService;

class TagPartsProviderService
{
    private $newsAggregatorService;
    
    public function __construct(NewsAggregatorService $newsAggregatorService)
    {
        $this->newsAggregatorService = $newsAggregatorService;
    }
    
	public function getParts($tag)
	{
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
				'values' => Article::getByTag($tag)->desc('published_at', Sort::DATE),
				'component' => 'articles'
			],
			[
				'id' => 'gallery_pictures',
				'label' => 'Галерея',
				'values' => GalleryPicture::getByTag($tag),
				'component' => 'gallery_pictures'
			],
			[
				'id' => 'comics',
				'label' => 'Комиксы',
				'values' => Collection::merge(
				    ComicIssue::getByTag($tag),
				    ComicSeries::getByTag($tag),
				    ComicStandalone::getByTag($tag)
				),
				'component' => 'comics'
			],
			[
				'id' => 'streams',
				'label' => 'Стримы',
				'values' => Stream::getByTag($tag),
				'component' => 'streams'
			],
			[
				'id' => 'events',
				'label' => 'События',
				'values' => Event::getByTag($tag)->desc('starts_at', Sort::DATE),
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
