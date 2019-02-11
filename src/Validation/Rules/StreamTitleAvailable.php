<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\TableFieldAvailable;

use App\Models\Stream;

class StreamTitleAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(Stream::getTable(), 'title', $id);
	}
}
