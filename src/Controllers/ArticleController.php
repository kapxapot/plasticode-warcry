<?php

namespace App\Controllers;

use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

/**
 * @property ArticleRepositoryInterface $articleRepository
 */
class ArticleController extends NewsSourceController
{
    /** @var ArticleRepositoryInterface */
    private $articleRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->articleRepository = $container->articleRepository;
    }

    public function item(SlimRequest $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];
        $cat = $args['cat'];

        $rebuild = $request->getQueryParam('rebuild', null);

        $article = $this->articleRepository->getBySlugOrAlias($id, $cat);

        if (!$article) {
            return $this->notFound($request, $response);
        }

        if ($rebuild !== null) {
            $article->resetDescription();
        }

        $params = $this->buildParams(
            [
                'game' => $article->game(),
                'sidebar' => [ 'stream', 'gallery', 'events', 'articles' ],
                'article_id' => $article->getId(),
                'large_image' => $article->largeImage(),
                'image' => $article->image(),
                'params' => [
                    'breadcrumbs' => $article->breadcrumbs(),
                    'disqus_url' => $this->linker->disqusArticle($article),
                    'disqus_id' => 'article' . $article->getId() . $cat,
                    'article' => $article,
                    'title' => $article->titleFull(),
                    'page_description' => $this->makeNewsPageDescription($article, 'articles.description_limit'),
                ],
            ]
        );

        return $this->render($response, 'main/articles/item.twig', $params);
    }
}
