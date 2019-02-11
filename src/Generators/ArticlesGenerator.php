<?php

namespace App\Generators;

use Respect\Validation\Validator as v;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;
use Plasticode\Util\Strings;

use App\Data\Taggable;

class ArticlesGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::ARTICLES;
	
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);

		$rules['name_ru'] = $this->rule('text');//->regex($cyr("'\(\):\-\.\|,\?!—«»"));
		$rules['parent_id'] = v::nonRecursiveParent($this->entity, $id);

		if (array_key_exists('name_en', $data) && array_key_exists('cat', $data)) {
			$rules['name_en'] = $this->rule('text')
				//->regex($this->rules->lat("':\-"))
				->articleNameCatAvailable($data['cat'], $id);
		}
		
		return $rules;
	}
	
	public function getOptions()
	{
	    $options = parent::getOptions();
	    
		$options['exclude'] = [ 'text' ];
		$options['admin_template'] = 'articles';
		
		return $options;
	}
	
	public function afterLoad($item)
	{
		$item['name_en_esc'] = Strings::fromSpaces($item['name_en']);
		
		if ($item['cat'] > 0) {
			$cat = $this->db->getCat($item['cat']);
			if ($cat) {
				$item['cat_ru'] = $cat['name_ru'];
				$item['cat_en'] = $cat['name_en'];
				$item['cat_en_esc'] = Strings::fromSpaces($cat['name_en']);
			}
		}
		
		$game = $this->db->getRootGame($item['game_id']);
		$parent = $this->db->getArticle($item['parent_id']);
		
		$parts = [ $game['name'] ];
		
		if ($parent && $parent['no_breadcrumb'] != 1) {
	        $parentParent = $this->db->getArticle($parent['parent_id']);
	        if ($parentParent && $parentParent['no_breadcrumb'] != 1) {
	            $parts[] = '...';
	        }

		    $parts[] = $parent['name_ru'];
		}
		
		$parts[] = $item['name_ru'];
		$partsStr = implode(' » ', $parts);
		
		$item['select_title'] = "[{$item['id']}] {$partsStr}";
		
		$item['tokens'] = $game['name'] . ' ' . $item['name_ru'];

		return $item;
	}
	
	public function beforeSave($data, $id = null)
	{
		$data['cache'] = null;
		$data['contents_cache'] = null;

		$data = $this->publishIfNeeded($data);		
		
		return $data;
	}

	public function afterSave($item, $data)
	{
	    if (!$item->name_en) {
    		$item->name_en = $item->name_ru;
    		$item->save();
    	}

		$this->notify($item, $data);
	}

	private function notify($item, $data)
	{
		if ($this->isJustPublished($item, $data) && $item->announce == 1) {
			if ($item->cat) {
				$catObj = $this->db->getCat($item->cat);
				if ($catObj) {
					$catName = $catObj['name_en'];
				}
			}
			
			$url = $this->linker->article($item->name_en, $catName);
			$url = $this->linker->abs($url);
			
			/*$this->telegram->sendMessage('warcry', "Опубликована статья:
<a href=\"{$url}\">{$item->name_ru}</a>");*/
		}
	}
}
