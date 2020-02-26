<?php

namespace App\Tests\Mocks\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Util\Date;

class ArticleRepositoryMock implements ArticleRepositoryInterface
{
    /** @var Collection */
    private $articles;

    public function __construct()
    {
        $this->pages = Collection::make(
            [
                new Page(
                    [
                        'id' => 1,
                        'slug' => 'about-us',
                        'title' => 'About us',
                        'text' => 'We are awesome. Work with us.',
                        'published' => 1,
                        'published_at' => Date::dbNow(),
                    ]
                ),
                new Page(
                    [
                        'id' => 2,
                        'slug' => 'illidan-stormrage',
                        'title' => 'Illidan Stormrage',
                        'text' => 'Illidan is a bad boy. Once a night elf, now a demon. Booo.',
                        'published' => 0,
                        'published_at' => null,
                    ]
                ),
            ]
        );
    }

    public function getBySlug(string $slug): ?Page
    {
        return $this->pages
            ->where('slug', $slug)
            ->first();
    }
}
