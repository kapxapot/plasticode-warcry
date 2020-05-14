<?php

namespace App\Services;

use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use App\Repositories\Interfaces\ComicSeriesRepositoryInterface;
use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Plasticode\Collections\Basic\TaggedCollection;

class ComicService
{
    private ComicIssueRepositoryInterface $comicIssueRepository;
    private ComicSeriesRepositoryInterface $comicSeriesRepository;
    private ComicStandaloneRepositoryInterface $comicStandaloneRepository;

    public function __construct(
        ComicIssueRepositoryInterface $comicIssueRepository,
        ComicSeriesRepositoryInterface $comicSeriesRepository,
        ComicStandaloneRepositoryInterface $comicStandaloneRepository
    )
    {
        $this->comicIssueRepository = $comicIssueRepository;
        $this->comicSeriesRepository = $comicSeriesRepository;
        $this->comicStandaloneRepository = $comicStandaloneRepository;
    }

    public function getAllByTag(string $tag) : TaggedCollection
    {
        return TaggedCollection::merge(
            $this->comicIssueRepository->getAllByTag($tag),
            $this->comicSeriesRepository->getAllByTag($tag),
            $this->comicStandaloneRepository->getAllByTag($tag)
        );
    }
}
