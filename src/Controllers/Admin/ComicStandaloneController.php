<?php

namespace App\Controllers\Admin;

use App\Auth\Interfaces\AuthInterface;
use App\Models\ComicStandalonePage;
use App\Repositories\Interfaces\ComicStandalonePageRepositoryInterface;
use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ComicStandaloneController extends ComicController
{
    private ComicStandalonePageRepositoryInterface $comicStandalonePageRepository;
    private ComicStandaloneRepositoryInterface $comicStandaloneRepository;

    private AuthInterface $auth;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->comicStandalonePageRepository = $container->comicStandalonePageRepository;
        $this->comicStandaloneRepository = $container->comicStandaloneRepository;

        $this->auth = $container->auth;
    }

    protected function createPage(array $context, string $imgType) : ComicStandalonePage
    {
        $comicIdField = ComicStandalonePage::comicIdField();
        $comicId = $context[$comicIdField] ?? null;

        Assert::greaterThan(
            $comicId,
            0,
            'Comic standalone id (\'comic_standalone_id\') must be provided.'
        );

        $comic = $this->comicStandaloneRepository->get($comicId);

        Assert::notNull($comic);

        $page = ComicStandalonePage::create(
            [
                $comicIdField => $comic->getId(),
                'number' => $comic->maxPageNumber() + 1,
                'pic_type' => $imgType,
            ]
        );

        $page->publish();
        $page->stamp($this->auth->getUser());

        return $this->comicStandalonePageRepository->save($page);
    }
}
