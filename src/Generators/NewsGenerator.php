<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

class NewsGenerator extends TaggableEntityGenerator
{
	use Publishable;

	public function beforeSave($data, $id = null)
	{
		$data['cache'] = null;

		$data = $this->publishIfNeeded($data);		

		return $data;
	}

	public function afterSave($item, $data)
	{
		$this->notify($item, $data);
	}

	private function notify($item, $data)
	{
		if ($this->isJustPublished($item, $data)) {
			$url = $this->linker->news($item->id);
			$url = $this->linker->abs($url);
			
			/*$this->telegram->sendMessage('warcry', "Опубликована новость:
<a href=\"{$url}\">{$item->title}</a>");*/
		}
	}
}
