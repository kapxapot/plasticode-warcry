<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\ForumMemberRepositoryInterface;
use App\Repositories\Interfaces\GalleryAuthorCategoryRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class GalleryAuthorHydrator extends Hydrator
{
    private ForumMemberRepositoryInterface $forumMemberRepository;
    private GalleryAuthorCategoryRepositoryInterface $galleryAuthorCategoryRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;

    private LinkerInterface $linker;
    private ParserInterface $parser;

    public function __construct(
        ForumMemberRepositoryInterface $forumMemberRepository,
        GalleryAuthorCategoryRepositoryInterface $galleryAuthorCategoryRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        $this->forumMemberRepository = $forumMemberRepository;
        $this->galleryAuthorCategoryRepository = $galleryAuthorCategoryRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;

        $this->linker = $linker;
        $this->parser = $parser;
    }

    /**
     * @param GalleryAuthor $entity
     */
    public function hydrate(DbModel $entity) : GalleryAuthor
    {
        return $entity
            ->withCategory(
                fn () =>
                $this
                    ->galleryAuthorCategoryRepository
                    ->get($entity->categoryId)
            )
            ->withPictures(
                fn () => $this->galleryPictureRepository->getAllByAuthor($entity)
            )
            ->withForumMember(
                fn () => $this->forumMemberRepository->getByName($entity->name)
            )
            ->withParsedDescription(
                fn () => $this->parse($entity->description)
            )
            ->withPageUrl(
                fn () => $this->linker->galleryAuthor($entity)
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
