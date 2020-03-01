<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Parsing\LinkMappers\Basic\TaggedLinkMapper;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Plasticode\Parsing\SlugChunk;
use Plasticode\Util\Numbers;
use Plasticode\ViewModels\UrlViewModel;

class CoordsLinkMapper extends TaggedLinkMapper
{
    /** @var LocationRepositoryInterface */
    private $locationRepository;

    public function __construct(
        LocationRepositoryInterface $locationRepository,
        RendererInterface $renderer,
        LinkerInterface $linker
    )
    {
        parent::__construct($renderer, $linker);

        $this->locationRepository = $locationRepository;
    }

    public function tag() : string
    {
        return 'coords';
    }

    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        if (count($otherChunks) < 2) {
            return null;
        }

        [$x, $y] = $otherChunks;

        $coordsText = '[' . round($x) . ',&nbsp;' . round($y) . ']';

        $slug = $slugChunk->slug();

        if (is_numeric($slug)) {
            $id = $slug;
        } else {
            $location = $this->locationRepository->getByName($slug);
            
            if (!$location) {
                return null;
            }

            $id = $location->getId();
        }

        if ($id <= 0) {
            return null;
        }

        $coords = '';
        
        $x = Numbers::parseFloat($x);
        $y = Numbers::parseFloat($y);
        
        if ($x > 0 && $y > 0) {
            $coords = ':' . ($x * 10) . ($y * 10);
        }
        
        $url = $this->linker->wowheadUrlRu('maps?data=' . $id . $coords);
        
        return $this->renderer->url(
            new UrlViewModel($url, $coordsText)
        );
    }
}
