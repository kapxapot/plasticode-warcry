<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;

use App\Data\Taggable;

class StreamsGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::STREAMS;
	
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
	    $rules['title'] = $this->rule('text')->streamTitleAvailable($id);
	    $rules['stream_id'] = $this->rule('extendedAlias')->streamIdAvailable($id);
	    
	    return $rules;
	}
	
	public function beforeSave($data, $id = null)
	{
		$data = $this->publishIfNeeded($data);		

		return $data;
	}
}
