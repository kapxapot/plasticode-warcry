<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\TableFieldAvailable;

use App\Models\GalleryAuthor;

class GalleryAuthorAliasAvailable extends TableFieldAvailable {
	public function __construct($id = null) {
		parent::__construct(GalleryAuthor::getTable(), 'alias', $id);
	}
}
