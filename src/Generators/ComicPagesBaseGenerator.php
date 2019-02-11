<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Util\Arrays;

abstract class ComicPagesBaseGenerator extends EntityGenerator
{
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
		$rules['picture'] = $this->optional('image');
		$rules['thumb'] = $this->rule('image');
		
		return $rules;
	}

	public function afterLoad($item)
	{
		$item['picture'] = $this->comics->getPictureUrl($item);
		$item['thumb'] = $this->comics->getThumbUrl($item);
		
		unset($item['type']);

		$item['page_url'] = $this->getPageUrl($item);

		return $item;
	}
	
	abstract protected function getPageUrl($item);

	public function beforeSave($data, $id = null)
	{
		if (isset($data['points'])) {
			unset($data['points']);
		}

		if (isset($data['picture'])) {
			unset($data['picture']);
		}

		if (isset($data['thumb'])) {
			unset($data['thumb']);
		}
				
		if (($data['number'] ?? 0) <= 0) {
		    $context = Arrays::filterKeys($data, [ 'comic_issue_id', 'comic_standalone_id' ]);
		    $data['number'] = $this->db->getMaxComicPageNumber($context, $id) + 1;
		}

		return $data;
	}
	
	public function afterSave($item, $data)
	{
		$this->comics->save($item, $data);
	}
	
	public function afterDelete($item)
	{
		$this->comics->delete($item);
	}
}
