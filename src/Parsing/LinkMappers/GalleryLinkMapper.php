<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Parsing\LinkMappers\Basic\TaggedLinkMapper;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Parsing\SlugChunk;
use Plasticode\Util\Strings;

class GalleryLinkMapper extends TaggedLinkMapper
{
    /** @var integer */
    private const DefaultMaxPictures = 5;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var GalleryPictureRepositoryInterface */
    private $galleryPictureRepository;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        GalleryPictureRepositoryInterface $galleryPictureRepository,
        RendererInterface $renderer,
        LinkerInterface $linker
    )
    {
        parent::__construct($renderer, $linker);

        $this->settingsProvider = $settingsProvider;
        $this->galleryPictureRepository = $galleryPictureRepository;
    }

    public function tag() : string
    {
        return 'gallery';
    }

    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $pictures = Collection::empty();
        
        $slug = $slugChunk->slug();
        $ids = Strings::explode($slug);

        /** @var integer */
        $maxPictures = $this->settingsProvider->get(
            'gallery.inline_limit',
            self::DefaultMaxPictures
        );

        /** @var boolean */
        $gridMode = false;

        /** @var string|null */
        $inlineTag = null;

        foreach ($otherChunks as $chunk) {
            if (is_numeric($chunk) && $chunk > 0) {
                $maxPictures = intval($chunk);
                continue;
            }
            
            if (mb_strtolower($chunk) == 'grid') {
                $gridMode = true;
            }
        }

        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                $pictures = $this
                    ->galleryPictureRepository
                    ->getAllByTag($id, $maxPictures);
                
                $inlineTag = $id;

                break;
            }
            
            $pic = $this->galleryPictureRepository->get($id);
            
            if ($pic) {
                $pictures = $pictures->add($pic);
            }
        }

        if ($pictures->isEmpty()) {
            return null;
        }

        $tagLink = $inlineTag
            ? $this->linker->tag($inlineTag, 'gallery_pictures')
            : null;

        return $this->renderer->component(
            'gallery_inline',
            [
                'pictures' => $pictures,
                'tag_link' => $tagLink,
                'grid_mode' => $gridMode,
            ]
        );
    }
}
