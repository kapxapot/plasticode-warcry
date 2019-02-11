<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;

use App\Data\Taggable;

class ComicSeriesGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::COMIC_SERIES;
	
	public function beforeSave($data, $id = null)
	{
		$data = $this->publishIfNeeded($data);		

		return $data;
	}

	public function afterLoad($item)
	{
		$item['page_url'] = $this->linker->comicSeries($item['alias']);

		return $item;
	}
}
