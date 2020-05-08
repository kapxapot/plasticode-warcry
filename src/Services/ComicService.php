<?php

namespace App\Services;

use App\Models\Comic;
use App\Models\ComicStandalone;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Util\Arrays;
use Webmozart\Assert\Assert;

class ComicService
{
    private ComicIssueRepositoryInterface $comicIssueRepository;
    private ComicStandaloneRepositoryInterface $comicStandaloneRepository;

    public function __construct(
        ComicIssueRepositoryInterface $comicIssueRepository,
        ComicStandaloneRepositoryInterface $comicStandaloneRepository
    )
    {
        $this->comicIssueRepository = $comicIssueRepository;
        $this->comicStandaloneRepository = $comicStandaloneRepository;
    }

    public function getComicByContext(array $data) : Comic
    {
        $issueId = $data['comic_issue_id'] ?? 0;
        $standaloneId = $data['comic_standalone_id'] ?? 0;

        Assert::true(
            $issueId > 0 || $standaloneId > 0,
            'Either comic_issue_id or comic_standalone_id must be provided.'
        );

        return ($issueId > 0)
            ? $this->comicIssueRepository->get($issueId)
            : $this->comicStandaloneRepository->get($standaloneId);
    }
}
