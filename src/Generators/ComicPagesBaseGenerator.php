<?php

namespace App\Generators;

use Plasticode\Exceptions\NotFoundException;
use Plasticode\Generators\EntityGenerator;

use App\Services\ComicService;

abstract class ComicPagesBaseGenerator extends EntityGenerator
{
    private $comicService;
    
	public function __construct($container, $entity)
	{
		parent::__construct($container, $entity);
		
		$this->comicService = new ComicService();
	}
	
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
	    $data = parent::beforeSave($data, $id);
	    
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
		    $comic = $this->comicService->getComicByContext($data);

		    if (!$comic) {
		        throw new NotFoundException('Comic not found!');
		    }
		    
		    $data['number'] = $comic->maxPageNumber() + 1;
		}

		return $data;
	}
	
	public function afterSave($item, $data)
	{
	    parent::afterSave($item, $data);
	    
		$this->comics->save($item, $data);
	}
	
	public function afterDelete($item)
	{
	    parent::afterDelete($item);
	    
		$this->comics->delete($item);
	}
}
