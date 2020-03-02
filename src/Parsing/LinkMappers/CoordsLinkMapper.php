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

        $slug = $slugChunk->slug();
        $locationId = $this->getLocationId($slug);

        if ($locationId <= 0) {
            return null;
        }

        [$x, $y] = $otherChunks;

        $coordsChunk = $this->buildCoordsChunk($x, $y);

        if (strlen($coordsChunk) == 0) {
            return null;
        }

        $url = $this->linker->wowheadUrlRu('maps?data=' . $locationId . $coordsChunk);

        $coordsText = '[' . round($x) . ', ' . round($y) . ']';

        return $this->renderer->url(
            new UrlViewModel(
                $url,
                $coordsText,
                null,
                'no-wrap'
            )
        );
    }

    private function getLocationId(string $slug) : int
    {
        if (is_numeric($slug)) {
            return intval($slug);
        }
        
        $location = $this->locationRepository->getByName($slug);
        
        return $location
            ? $location->getId()
            : 0;
    }

    /**
     * Builds coords url chunk.
     *
     * @param mixed $x
     * @param mixed $y
     * @return string|null
     */
    private function buildCoordsChunk($x, $y) : ?string
    {
        $x = Numbers::parseFloat($x);
        $y = Numbers::parseFloat($y);
        
        return ($x > 0 && $y > 0)
            ? ':' . ($x * 10) . ($y * 10)
            : null;
    }
}
