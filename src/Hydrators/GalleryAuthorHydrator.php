<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\ForumMemberRepositoryInterface;
use App\Repositories\Interfaces\GalleryAuthorCategoryRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class GalleryAuthorHydrator extends ParsingHydrator
{
    private ForumMemberRepositoryInterface $forumMemberRepository;
    private GalleryAuthorCategoryRepositoryInterface $galleryAuthorCategoryRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;

    private LinkerInterface $linker;

    public function __construct(
        ForumMemberRepositoryInterface $forumMemberRepository,
        GalleryAuthorCategoryRepositoryInterface $galleryAuthorCategoryRepository,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct($parser);

        $this->forumMemberRepository = $forumMemberRepository;
        $this->galleryAuthorCategoryRepository = $galleryAuthorCategoryRepository;
        $this->galleryPictureRepository = $galleryPictureRepository;

        $this->linker = $linker;
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
                fn () => $this->parse($entity->description)->text
            )
            ->withPageUrl(
                fn () => $this->linker->galleryAuthor($entity)
            );
    }
}
