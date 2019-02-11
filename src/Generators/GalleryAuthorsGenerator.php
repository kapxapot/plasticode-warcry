<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;

class GalleryAuthorsGenerator extends EntityGenerator
{
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
		$rules['name'] = $this->rule('text')->galleryAuthorNameAvailable($id);

		if (array_key_exists('alias', $data)) {
			$rules['alias'] = $this->rule('alias')->galleryAuthorAliasAvailable($id);
		}
		
		return $rules;
	}
	
	public function getOptions()
	{
	    $options = parent::getOptions();
	    
		$options['admin_uri'] = 'gallery';
		$options['admin_template'] = 'entity_with_upload';
		$options['admin_args'] = [
		    'upload_path' => 'admin.gallery.upload',
		];

		return $options;
	}
	
	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);

		$item['context_field'] = 'author_id';

		return $item;
	}
}
