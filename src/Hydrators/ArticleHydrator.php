<?php

namespace App\Hydrators;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ArticleHydrator extends Hydrator
{
    private ArticleCategoryRepositoryInterface $articleCategoryRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ArticleCategoryRepositoryInterface $articleCategoryRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->articleCategoryRepository = $articleCategoryRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Article $entity
     */
    public function hydrate(DbModel $entity) : Article
    {
        return $entity
            ->withCategory(
                $this->articleCategoryRepository->get($entity->cat)
            )
            ->withCreator(
                $this->userRepository->get($entity->createdBy)
            );
    }
}
