<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicPage;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

abstract class ComicPageHydrator extends Hydrator
{
    protected LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
    {
        $this->linker = $linker;
    }

    /**
     * @param ComicPage $entity
     */
    public function hydrate(DbModel $entity) : ComicPage
    {
        return $entity
            ->withUrl(
                fn () => $this->linker->comicPageImg($entity)
            )
            ->withThumbUrl(
                fn () => $this->linker->comicThumbImg($entity)
            )
            ->withExt(
                fn () => $this->linker->getImageExtension($entity->picType)
            );
    }
}
