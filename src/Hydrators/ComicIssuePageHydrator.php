<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicIssuePage;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use Plasticode\Models\DbModel;

class ComicIssuePageHydrator extends ComicPageHydrator
{
    private ComicIssueRepositoryInterface $comicIssueRepository;

    public function __construct(
        ComicIssueRepositoryInterface $comicIssueRepository,
        LinkerInterface $linker
    )
    {
        parent::__construct($linker);

        $this->comicIssueRepository = $comicIssueRepository;
    }

    /**
     * @param ComicIssuePage $entity
     */
    public function hydrate(DbModel $entity) : ComicIssuePage
    {
        /**
         * @var ComicIssuePage
         */
        $entity = parent::hydrate($entity);

        return $entity
            ->withComic(
                fn () => $this->comicIssueRepository->get($entity->comicId())
            )
            ->withPageUrl(
                fn () => $this->linker->comicIssuePage($entity)
            );
    }
}
