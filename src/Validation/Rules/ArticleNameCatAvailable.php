<?php

namespace App\Validation\Rules;

use Plasticode\Validation\Rules\ContainerRule;

use App\Models\Article;

class ArticleNameCatAvailable extends ContainerRule
{
	private $cat;
	private $id;
	
	public function __construct($cat = null, $id = null)
	{
		$this->cat = $cat;
		$this->id = $id;
	}

	public function validate($input)
	{
	    $article = Article::lookup($input, $this->cat, $this->id)->one();

		return $article === null;
	}
}
