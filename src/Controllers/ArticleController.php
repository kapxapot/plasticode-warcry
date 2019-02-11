<?php

namespace App\Controllers;

use Plasticode\Util\Strings;

use App\Core\Article;

class ArticleController extends BaseController
{
	public function item($request, $response, $args)
	{
		$id = $args['id'];
		$cat = $args['cat'];

		$rebuild = $request->getQueryParam('rebuild', false);

		$article = new Article($this->container, $id, $cat, $rebuild);

		if (!$article->data) {
			return $this->notFound($request, $response);
		}

		$id = $article->id;

		$article = $this->builder->buildArticle($article);

		$params = $this->buildParams([
			'game' => $article['game'],
			'sidebar' => [ 'stream', 'gallery', 'events', 'articles' ],
			'article_id' => $id,
			'large_image' => $article['parsed']['large_image'],
			'image' => $article['parsed']['image'],
			'params' => [
				'breadcrumbs' => $article['breadcrumbs'],
				'disqus_url' => $this->linker->disqusArticle($article),
				'disqus_id' => 'article' . $id . $cat,
				'article' => $article,
				'title' => $article['title'],
				'page_description' => $article['description'],
			],
		]);

		return $this->view->render($response, 'main/articles/item.twig', $params);
	}
	
	/*protected function getArticle($args)
	{
		$id = $args['id'];
		$cat = $args['cat'];

		$id = Strings::toSpaces($id);
		$cat = Strings::toSpaces($cat);

		return $this->db->getArticle($id, $cat);
	}

	public function source($request, $response, $args)
	{
		$article = $this->getArticle($args);

		if (!$article) {
			return $this->notFound($request, $response);
		}

		return $article['text'];
	}*/
}
