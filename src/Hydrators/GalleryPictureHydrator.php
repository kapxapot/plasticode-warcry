<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class GalleryPictureHydrator extends Hydrator
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;
    private ParserInterface $parser;

    public function __construct(
        GalleryAuthorRepositoryInterface $galleryAuthorRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        $this->galleryAuthorRepository = $galleryAuthorRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;
        $this->parser = $parser;
    }

    /**
     * @param GalleryPicture $entity
     */
    public function hydrate(DbModel $entity) : GalleryPicture
    {
        return $entity
            ->withAuthor(
                fn () => $this->galleryAuthorRepository->get($entity->authorId)
            )
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            )
            ->withParsedDescription(
                fn () => $this->parse($entity->description)
            )
            ->withPrev(
                fn () => $this->galleryPictureRepository->getPrevSibling($entity)
            )
            ->withNext(
                fn () => $this->galleryPictureRepository->getNextSibling($entity)
            )
            ->withExt(
                fn () => $this->linker->getImageExtension($entity->pictureType)
            )
            ->withUrl(
                fn () => $this->linker->galleryPictureImg($entity)
            )
            ->withThumbUrl(
                fn () => $this->linker->galleryThumbImg($entity)
            )
            ->withPageUrl(
                fn () => $this->linker->galleryPicture($entity)
            );
    }

    private function parse(?string $text) : ?string
    {
        if (strlen($text) == 0) {
            return null;
        }

        $context = $this->parser->parse($text);
        $context = $this->parser->renderLinks($context);

        return $context
            ? $context->text
            : null;
    }
}
