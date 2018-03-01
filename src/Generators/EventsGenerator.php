<?php

namespace App\Generators;

use Plasticode\Generators\PublishableGenerator;

use App\Data\Taggable;

class EventsGenerator extends PublishableGenerator {
	protected $taggable = Taggable::EVENTS;
	
	public function beforeSave($data, $id = null) {
		$data['cache'] = null;

		$data = $this->publishIfNeeded($data);		

		return $data;
	}
}
