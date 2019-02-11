<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

use App\Models\Event;

class EventsGenerator extends TaggableEntityGenerator
{
	use Publishable;

	public function beforeSave($data, $id = null)
	{
		$data['cache'] = null;

		$data = $this->publishIfNeeded($data);		

		return $data;
	}
}
