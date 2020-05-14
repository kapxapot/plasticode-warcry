<?php

namespace App\Controllers\Admin;

use App\Auth\Interfaces\AuthInterface;
use App\Models\ComicIssuePage;
use App\Repositories\Interfaces\ComicIssuePageRepositoryInterface;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ComicIssueController extends ComicController
{
    private ComicIssuePageRepositoryInterface $comicIssuePageRepository;
    private ComicIssueRepositoryInterface $comicIssueRepository;

    private AuthInterface $auth;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->comicIssuePageRepository = $container->comicIssuePageRepository;
        $this->comicIssueRepository = $container->comicIssueRepository;

        $this->auth = $container->auth;
    }

    protected function createPage(array $context, string $imgType) : ComicIssuePage
    {
        $comicIdField = ComicIssuePage::comicIdField();
        $comicId = $context[$comicIdField] ?? null;

        Assert::greaterThan(
            $comicId,
            0,
            'Comic issue id (\'comic_issue_id\') must be provided.'
        );

        $comic = $this->comicIssueRepository->get($comicId);

        Assert::notNull($comic);

        $page = ComicIssuePage::create(
            [
                $comicIdField => $comic->getId(),
                'number' => $comic->maxPageNumber() + 1,
                'pic_type' => $imgType,
            ]
        );

        $page->publish();
        $page->stamp($this->auth->getUser());

        return $this->comicIssuePageRepository->save($page);
    }
}
