<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Article;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;

class ArticleHydrator extends NewsSourceHydrator
{
    private ArticleCategoryRepositoryInterface $articleCategoryRepository;
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(
        ArticleCategoryRepositoryInterface $articleCategoryRepository,
        ArticleRepositoryInterface $articleRepository,
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser,
        TagsConfigInterface $tagsConfig
    )
    {
        parent::__construct(
            $gameRepository,
            $userRepository,
            $cutParser,
            $linker,
            $parser,
            $tagsConfig
        );

        $this->articleCategoryRepository = $articleCategoryRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * @param Article $entity
     */
    public function hydrate(DbModel $entity) : Article
    {
        /** @var Article */
        $entity = parent::hydrate($entity);

        return $entity
            ->withCategory(
                fn () => $this->articleCategoryRepository->get($entity->cat)
            )
            ->withChildren(
                fn () => $this->articleRepository->getChildren($entity)
            )
            ->withParent(
                fn () => $this->articleRepository->get($entity->parentId)
            )
            ->withUrl(
                fn () => $this->buildUrl($entity)
            );
    }

    private function buildUrl(Article $article) : string
    {
        $cat = $article->category();

        return $this->linker->article(
            $article->nameEn,
            $cat ? $cat->nameEn : null
        );
    }
}
