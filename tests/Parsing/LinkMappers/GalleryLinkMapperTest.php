<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Parsing\LinkMappers\GalleryLinkMapper;
use App\Testing\Mocks\LinkerMock;
use App\Testing\Mocks\Repositories\GalleryAuthorRepositoryMock;
use App\Testing\Mocks\Repositories\GalleryPictureRepositoryMock;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Mocks\SettingsProviderMock;
use App\Testing\Seeders\GalleryAuthorSeeder;
use App\Testing\Seeders\GalleryPictureSeeder;
use App\Testing\Seeders\GameSeeder;
use App\Tests\BaseRenderTestCase;

final class GalleryLinkMapperTest extends BaseRenderTestCase
{
    private LinkerInterface $linker;
    private GalleryLinkMapper $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->linker = new LinkerMock();

        $settingsProvider = new SettingsProviderMock();

        $galleryAuthorRepository = new GalleryAuthorRepositoryMock(
            new GalleryAuthorSeeder()
        );

        $gameRepository = new GameRepositoryMock(
            new GameSeeder()
        );

        $galleryPictureRepository = new GalleryPictureRepositoryMock(
            new GalleryPictureSeeder(
                $galleryAuthorRepository,
                $gameRepository,
                $this->linker
            )
        );

        $this->mapper = new GalleryLinkMapper(
            $settingsProvider,
            $galleryPictureRepository,
            $this->renderer,
            $this->linker
        );
    }

    protected function tearDown() : void
    {
        unset($this->mapper);
        unset($this->linker);

        parent::tearDown();
    }

    /**
     * @dataProvider mapProvider
     */
    public function testMap(array $chunks, ?string $expected) : void
    {
        $this->assertEquals(
            $expected,
            $this->mapper->map($chunks)
        );
    }

    public function mapProvider() : array
    {
        return [
            'simple' => [
                ['gallery:1,2'],
                '<div class="flex-wrapper gallery gallery--uniform"><div class="flex-item flex-item-shaded overlay-wrapper"><a href="http://abs/gallery/picture/1" class="colorbox" title="Sexy elf, автор: Author"><img src="http://abs/gallery/picture/thumb/1" alt="Sexy elf" class="card-image" /><span class="overlay overlay-full">Sexy elf</span></a></div><div class="flex-item flex-item-shaded overlay-wrapper"><a href="http://abs/gallery/picture/2" class="colorbox" title="Dead man, автор: Author"><img src="http://abs/gallery/picture/thumb/2" alt="Dead man" class="card-image" /><span class="overlay overlay-full">Dead man</span></a></div></div>'
            ],
            'grid' => [
                ['gallery:1,2', '1', 'grid'],
                '<div class="grid gallery-grid" id="gallery-grid"><div class="grid-item ratio-w2 ratio-h1" data-id="1"><img class="lozad" data-src="http://abs/gallery/picture/thumb/1" alt="Sexy elf" style="background-color: rgb(255, 255, 255);" /><a class="grid-item__overlay p-2 colorbox" href="http://abs/gallery/picture/1">Sexy elf<br/>(Author)</a></div><div class="grid-item ratio-w1 ratio-h3" data-id="2"><img class="lozad" data-src="http://abs/gallery/picture/thumb/2" alt="Dead man" style="background-color: rgb(255, 255, 255);" /><a class="grid-item__overlay p-2 colorbox" href="http://abs/gallery/picture/2">Dead man<br/>(Author)</a></div></div>'
            ],
            'known_tag' => [
                ['gallery:Elves'],
                '<div class="flex-wrapper gallery gallery--uniform"><div class="flex-item flex-item-shaded overlay-wrapper"><a href="http://abs/gallery/picture/1" class="colorbox" title="Sexy elf, автор: Author"><img src="http://abs/gallery/picture/thumb/1" alt="Sexy elf" class="card-image" /><span class="overlay overlay-full">Sexy elf</span></a></div></div><div class="flex-center mt-2 mb-1"><a class="btn btn-lg btn-default" href="http://abs/tags/Elves" role="button">Все картинки &raquo;&raquo;</a></div>'
            ],
            'unknown_tag' => [
                ['gallery:Orcs'],
                null
            ],
        ];
    }
}
