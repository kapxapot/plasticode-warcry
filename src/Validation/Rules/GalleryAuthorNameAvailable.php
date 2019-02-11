<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\TableFieldAvailable;

use App\Models\GalleryAuthor;

class GalleryAuthorNameAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(GalleryAuthor::getTable(), 'name', $id);
	}
}
