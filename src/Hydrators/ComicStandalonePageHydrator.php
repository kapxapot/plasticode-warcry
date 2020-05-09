<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicStandalonePage;
use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Plasticode\Models\DbModel;

class ComicStandalonePageHydrator extends ComicPageHydrator
{
    private ComicStandaloneRepositoryInterface $comicStandaloneRepository;

    public function __construct(
        ComicStandaloneRepositoryInterface $comicStandaloneRepository,
        LinkerInterface $linker
    )
    {
        parent::__construct($linker);

        $this->comicIssueRepository = $comicStandaloneRepository;
    }

    /**
     * @param ComicStandalonePage $entity
     */
    public function hydrate(DbModel $entity) : ComicStandalonePage
    {
        /**
         * @var ComicStandalonePage
         */
        $entity = parent::hydrate($entity);

        return $entity
            ->withComic(
                fn () => $this->comicStandaloneRepository->get($entity->comicId())
            )
            ->withPageUrl(
                fn () => $this->linker->comicStandalonePage($entity)
            );
    }
}
