<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Parsing\LinkMappers\GalleryLinkMapper;
use App\Tests\BaseRenderTestCase;
use App\Tests\Mocks\LinkerMock;
use App\Tests\Mocks\Repositories\GalleryAuthorRepositoryMock;
use App\Tests\Mocks\Repositories\GalleryPictureRepositoryMock;
use App\Tests\Mocks\SettingsProviderMock;
use App\Tests\Seeders\GalleryAuthorSeeder;
use App\Tests\Seeders\GalleryPictureSeeder;
use App\Tests\Traits\WithDb;

final class GalleryLinkMapperTest extends BaseRenderTestCase
{
    use WithDb;

    /** @var LinkerInterface */
    private $linker;

    /** @var GalleryLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->linker = new LinkerMock();

        $this->initModels();

        $settingsProvider = new SettingsProviderMock();

        $galleryAuthorRepository = new GalleryAuthorRepositoryMock(
            new GalleryAuthorSeeder()
        );
        
        $galleryPictureRepository = new GalleryPictureRepositoryMock(
            new GalleryPictureSeeder($galleryAuthorRepository)
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
            [
                ['gallery:1,2'],
                '<div class="flex-wrapper gallery gallery--uniform"><div class="flex-item flex-item-shaded overlay-wrapper"><a href="http://abs/gallery/picture/1" class="colorbox" title="Sexy elf, автор: Author"><img src="http://abs/gallery/picture/thumb/1" alt="Sexy elf" class="card-image" /><span class="overlay overlay-full">Sexy elf</span></a></div><div class="flex-item flex-item-shaded overlay-wrapper"><a href="http://abs/gallery/picture/2" class="colorbox" title="Dead man, автор: Author"><img src="http://abs/gallery/picture/thumb/2" alt="Dead man" class="card-image" /><span class="overlay overlay-full">Dead man</span></a></div></div>'
            ],
            [
                ['gallery:1,2', '1', 'grid'],
                '<div class="grid gallery-grid" id="gallery-grid"><div class="grid-item" data-id="1"><img class="lozad" data-src="http://abs/gallery/picture/thumb/1" alt="Sexy elf" style="background-color: rgb(255, 255, 255);" /><a class="grid-item__overlay p-2 colorbox" href="http://abs/gallery/picture/1">Sexy elf<br/>(Author)</a></div><div class="grid-item" data-id="2"><img class="lozad" data-src="http://abs/gallery/picture/thumb/2" alt="Dead man" style="background-color: rgb(255, 255, 255);" /><a class="grid-item__overlay p-2 colorbox" href="http://abs/gallery/picture/2">Dead man<br/>(Author)</a></div></div>'
            ],
            [
                ['gallery:Elves'],
                '<div class="flex-wrapper gallery gallery--uniform"><div class="flex-item flex-item-shaded overlay-wrapper"><a href="http://abs/gallery/picture/1" class="colorbox" title="Sexy elf, автор: Author"><img src="http://abs/gallery/picture/thumb/1" alt="Sexy elf" class="card-image" /><span class="overlay overlay-full">Sexy elf</span></a></div></div><div class="flex-center mt-2 mb-1"><a class="btn btn-lg btn-default" href="http://abs/tags/Elves" role="button">Все картинки &raquo;&raquo;</a></div>'
            ],
            [
                ['gallery:Orcs'],
                null
            ],
        ];
    }
}
