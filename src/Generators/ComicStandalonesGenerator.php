<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;

use App\Data\Taggable;

class ComicStandalonesGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::COMIC_STANDALONES;

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
		$data = $this->publishIfNeeded($data);		

		return $data;
	}

	public function afterLoad($item)
	{
	    $item = parent::afterLoad($item);
	    
		$item['page_url'] = $this->linker->comicStandalone($item['alias']);
		$item['context_field'] = 'comic_standalone_id';

		return $item;
	}
}
