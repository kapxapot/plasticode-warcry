<?php

namespace App\Controllers;

use Plasticode\Core\Core;

use App\Models\Article;

class SearchController
{
	public function search($request, $response, $args)
	{
	    $query = $args['query'];
	    
	    $result = [];
	    
	    $articles = Article::search($query)
	        ->map(function ($article) {
	            return [
	                'type' => 'article',
	                'data' => $article->serialize(),
	            ];
	        });
        
	    $result = array_merge($result, $articles->toArray());

		return Core::json($response, $result);
	}
}
