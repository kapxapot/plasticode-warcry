<?php

namespace App\Generators;

use Plasticode\Util\Strings;
use Plasticode\Generators\PublishableGenerator;

class ArticlesGenerator extends PublishableGenerator {
	public function getRules($data, $id = null) {
		$rules = [
			'name_ru' => $this->rule('text'),//->regex($cyr("'\(\):\-\.\|,\?!—«»"));
		];

		if (array_key_exists('name_en', $data) && array_key_exists('cat', $data)) {
			$rules['name_en'] = $this->rule('text')
				//->regex($this->rules->lat("':\-"))
				->articleNameCatAvailable($data['cat'], $id);
		}
		
		return $rules;
	}
	
	public function getOptions() {
		return [
			'exclude' => [ 'text' ],
			'admin_template' => 'article',
		];
	}
	
	public function afterLoad($item) {
		$item['name_en_esc'] = Strings::fromSpaces($item['name_en']);
		
		if ($item['cat'] > 0) {
			$cat = $this->db->getCat($item['cat']);
			if ($cat) {
				$item['cat_ru'] = $cat['name_ru'];
				$item['cat_en'] = $cat['name_en'];
				$item['cat_en_esc'] = Strings::fromSpaces($cat['name_en']);
			}
		}

		return $item;
	}
	
	public function beforeSave($data, $id = null) {
		$data['cache'] = null;
		$data['contents_cache'] = null;
		
		$data['name_en'] = $data['name_en'] ?? $data['name_ru'];
		
		$data = $this->publishIfNeeded($data);		
		
		return $data;
	}

	public function afterSave($item, $data) {
		$this->notify($item, $data);
	}

	private function notify($item, $data) {
		if ($this->isJustPublished($item, $data) && $item->announce == 1) {
			if ($item->cat) {
				$catObj = $this->db->getCat($item->cat);
				if ($catObj) {
					$catName = $catObj['name_en'];
				}
			}
			
			$url = $this->linker->article($item->name_en, $catName);
			$url = $this->linker->abs($url);
			
			$this->telegram->sendMessage('warcry', "Опубликована статья:
<a href=\"{$url}\">{$item->name_ru}</a>");
		}
	}
}
