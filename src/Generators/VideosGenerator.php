<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

class VideosGenerator extends TaggableEntityGenerator
{
	use Publishable;

	public function beforeSave($data, $id = null)
	{
	    $data = parent::beforeSave($data, $id);

		$data = $this->publishIfNeeded($data);		

		return $data;
	}
}
