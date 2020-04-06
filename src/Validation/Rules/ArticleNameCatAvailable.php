<?php

namespace App\Validation\Rules;

use App\Models\Article;
use Respect\Validation\Rules\AbstractRule;

class ArticleNameCatAvailable extends AbstractRule
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

        return is_null($article);
    }
}
