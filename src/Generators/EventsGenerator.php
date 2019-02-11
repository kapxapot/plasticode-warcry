<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;

use App\Data\Taggable;

class EventsGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::EVENTS;
	
	public function beforeSave($data, $id = null)
	{
		$data['cache'] = null;

		$data = $this->publishIfNeeded($data);		

		return $data;
	}
}
