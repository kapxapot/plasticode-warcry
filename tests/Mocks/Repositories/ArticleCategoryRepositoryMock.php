<?php

namespace App\Tests\Mocks\Repositories;

use App\Models\ArticleCategory;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

class ArticleCategoryRepositoryMock implements ArticleCategoryRepositoryInterface
{
    /** @var Collection */
    private $categories;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->categories = Collection::make($seeder->seed());
    }

    public function getByName(string $name) : ?ArticleCategory
    {
        return $this
            ->categories
            ->where('name_en', $name)
            ->first();
    }
}
