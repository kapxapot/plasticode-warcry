<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Util\Arrays;

use App\Models\ComicIssue;
use App\Models\ComicStandalone;

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
	    $item = parent::afterLoad($item);
	    
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
		    if (isset($data['comic_issue_id'])) {
		        $comic = ComicIssue::get($data['comic_issue_id']);
		    }
		    elseif (isset($data['comic_standalone_id'])) {
		        $comic = ComicStandalone::get($data['comic_standalone_id']);
		    }
		    else {
		        throw new \InvalidArgumentException('Either comic_issue_id or comic_standalone_id must be provided.');
		    }
		    
		    if (!$comic) {
		        throw new \InvalidArgumentException('Comic not found!');
		    }
		    
		    $data['number'] = $comic->maxPageNumber() + 1;
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
