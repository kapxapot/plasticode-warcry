<?php

namespace App\Controllers;

use Plasticode\Util\Strings;

use App\Models\Article;

class ArticleController extends Controller
{
	public function item($request, $response, $args)
	{
		$id = $args['id'];
		$cat = $args['cat'];

		$rebuild = $request->getQueryParam('rebuild', false);

		$article = Article::getByName($id, $cat);

		if (!$article) {
			return $this->notFound($request, $response);
		}

        if ($rebuild) {
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
