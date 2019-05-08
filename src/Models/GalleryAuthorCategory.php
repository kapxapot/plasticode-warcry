<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class GalleryAuthorCategory extends DbModel
{
    protected static $sortField = 'position';

	// PROPS
	
	public function authors()
	{
	    return $this->lazy(function() {
    	    return GalleryAuthor::getAllPublishedByCategory($this->id);
	    });
	}
}
