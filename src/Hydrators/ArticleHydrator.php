<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Article;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ArticleHydrator extends Hydrator
{
    private ArticleCategoryRepositoryInterface $articleCategoryRepository;
    private ArticleRepositoryInterface $articleRepository;
    private UserRepositoryInterface $userRepository;

    private LinkerInterface $linker;

    public function __construct(
        ArticleCategoryRepositoryInterface $articleCategoryRepository,
        ArticleRepositoryInterface $articleRepository,
        UserRepositoryInterface $userRepository,
        LinkerInterface $linker
    )
    {
        $this->articleCategoryRepository = $articleCategoryRepository;
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;

        $this->linker = $linker;
    }

    /**
     * @param Article $entity
     */
    public function hydrate(DbModel $entity) : Article
    {
        return $entity
            ->withCategory(
                fn () => $this->articleCategoryRepository->get($entity->cat)
            )
            ->withUrl(
                function () use ($entity) {
                    $cat = $entity->category();

                    return $this->linker->article(
                        $this->nameEn,
                        $cat ? $cat->nameEn : null
                    );
                }
            )
            ->withChildren(
                fn () => $this->articleRepository->getChildren($entity)
            )
            ->withParent(
                fn () => $this->articleRepository->get($entity->parentId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }
}
