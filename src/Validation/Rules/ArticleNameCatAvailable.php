<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\ContainerRule;

use App\Data\Tables;

class ArticleNameCatAvailable extends ContainerRule {
	private $cat;
	private $id;
	
	public function __construct($cat = null, $id = null) {
		$this->cat = $cat;
		$this->id = $id;
	}

	public function validate($input) {
		$query = $this->container->db->forTable(TABLES::ARTICLES)
			->where('name_en', $input);
		
		if ($this->cat) {
			$query = $query->where('cat', $this->cat);
		}
		else {
			$query = $query->whereNull('cat');
		}
		
		if ($this->id) {
			$query = $query->whereNotEqual('id', $this->id);
		}

		return $query->count() == 0;
	}
}
