<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class ArticleController extends NewsSourceController
{
    private ArticleRepositoryInterface $articleRepository;
    private NotFoundHandler $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->articleRepository = $container->articleRepository;
        $this->notFoundHandler = $container->notFoundHandler;
    }

    public function item(
        Request $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];
        $cat = $args['cat'];

        $rebuild = $request->getQueryParam('rebuild', null);

        $article = $this->articleRepository->getBySlugOrAlias($id, $cat);

        if (!$article) {
            return ($this->notFoundHandler)($request, $response);
        }

        if ($rebuild !== null) {
            // Todo: reset article description
            // Currently, there's no caching
        }

        $params = $this->buildParams(
            [
                'game' => $article->game(),
                'sidebar' => ['stream', 'gallery', 'events', 'articles'],
                'article_id' => $article->getId(),
                'large_image' => $article->largeImage(),
                'image' => $article->image(),
                'params' => [
                    'breadcrumbs' => $article->breadcrumbs(),
                    'disqus_url' => $this->linker->disqusArticle($article),
                    'disqus_id' => 'article' . $article->getId() . $cat,
                    'article' => $article,
                    'title' => $article->titleFull(),
                    'page_description' => $this->makeNewsPageDescription(
                        $article,
                        'articles.description_limit'
                    ),
                    'canonical_url' => $this->linker->abs($article->url()),
                ],
            ]
        );

        return $this->render($response, 'main/articles/item.twig', $params);
    }
}
