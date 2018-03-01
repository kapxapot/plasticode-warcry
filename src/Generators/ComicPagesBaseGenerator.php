<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;

class ComicPagesBaseGenerator extends EntityGenerator {
	public function afterLoad($item) {
		$item['picture'] = $this->comics->getPictureUrl($item);
		$item['thumb'] = $this->comics->getThumbUrl($item);
		
		unset($item['type']);

		return $item;
	}

	public function beforeSave($data, $id = null) {
		if (isset($data['points'])) {
			unset($data['points']);
		}

		if (isset($data['picture'])) {
			unset($data['picture']);
		}

		if (isset($data['thumb'])) {
			unset($data['thumb']);
		}
		
		return $data;
	}
	
	public function afterSave($item, $data) {
		$this->comics->save($item, $data);
	}
	
	public function afterDelete($item) {
		$this->comics->delete($item);
	}
}
