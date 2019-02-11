<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\ContainerRule;

use App\Models\Article;

class ArticleNameCatAvailable extends ContainerRule {
	private $cat;
	private $id;
	
	public function __construct($cat = null, $id = null) {
		$this->cat = $cat;
		$this->id = $id;
	}

	public function validate($input) {
	    $article = Article::getBy(function ($query) use ($input) {
    		$query = $query->where('name_en', $input);
    		
    		if ($this->cat) {
    			$query = $query->where('cat', $this->cat);
    		}
    		else {
    			$query = $query->whereNull('cat');
    		}
    		
    		if ($this->id) {
    			$query = $query->whereNotEqual('id', $this->id);
    		}
    		
    		return $query;
	    });

		return $article === null;
	}
}
