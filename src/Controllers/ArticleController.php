<?php

namespace App\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
	public function item($request, $response, $args)
	{
		$id = $args['id'];
		$cat = $args['cat'];

		$rebuild = $request->getQueryParam('rebuild', null);

		$article = Article::getByNameOrAlias($id, $cat);

		if (!$article) {
			return $this->notFound($request, $response);
		}

        if ($rebuild !== null) {
            $article->resetDescription();
        }
        
        $parsed = $article->parsed();

		$params = $this->buildParams([
			'game' => $article->game(),
			'sidebar' => [ 'stream', 'gallery', 'events', 'articles' ],
			'article_id' => $article->getId(),
			'large_image' => $parsed['large_image'],
			'image' => $parsed['image'],
			'params' => [
				'breadcrumbs' => $article->breadcrumbs(),
				'disqus_url' => $this->linker->disqusArticle($article),
				'disqus_id' => 'article' . $article->getId() . $cat,
				'article' => $article,
				'title' => $article->titleFull(),
				'page_description' => $this->makePageDescription($parsed['text'], 'articles.description_limit'),
			],
		]);

		return $this->view->render($response, 'main/articles/item.twig', $params);
	}
}
