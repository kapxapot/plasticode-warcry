<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

use App\Models\ComicSeries;

class ComicSeriesGenerator extends TaggableEntityGenerator
{
	use Publishable;

	public function beforeSave($data, $id = null)
	{
		$data = $this->publishIfNeeded($data);		

		return $data;
	}

	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);
	    
	    $series = ComicSeries::get($item['id']);
	    
		$item['page_url'] = $this->linker->comicSeries($series);

		return $item;
	}
}
