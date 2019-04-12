<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

use App\Models\ComicStandalone;

class ComicStandalonesGenerator extends TaggableEntityGenerator
{
	use Publishable;

	public function getOptions()
	{
	    $options = parent::getOptions();
	    
		$options['admin_template'] = 'entity_with_upload';
		$options['admin_args'] = [
		    'upload_path' => 'admin.comics.upload',
		];
	    
	    return $options;
	}
	
	public function beforeSave($data, $id = null)
	{
	    $data = parent::beforeSave($data, $id);

		$data = $this->publishIfNeeded($data);		

		return $data;
	}

	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);
	    
	    $comic = ComicStandalone::get($item['id']);
	    
		$item['page_url'] = $this->linker->comicStandalone($comic);
		$item['context_field'] = 'comic_standalone_id';

		return $item;
	}
}
