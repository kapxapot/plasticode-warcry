<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\ArticleCategoryCollection;
use App\Models\ArticleCategory;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class ArticleCategoryRepositoryMock implements ArticleCategoryRepositoryInterface
{
    private ArticleCategoryCollection $categories;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->categories = ArticleCategoryCollection::make($seeder->seed());
    }

    public function getByName(string $name) : ?ArticleCategory
    {
        return $this
            ->categories
            ->first('name_en', $name);
    }
}
