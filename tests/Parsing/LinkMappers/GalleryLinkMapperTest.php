<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Parsing\LinkMappers\GalleryLinkMapper;
use App\Tests\BaseRenderTestCase;
use App\Tests\Mocks\LinkerMock;
use App\Tests\Mocks\Repositories\GalleryPictureRepositoryMock;
use App\Tests\Mocks\SettingsProviderMock;
use App\Tests\Seeders\GalleryPictureSeeder;
use Plasticode\Auth\Auth;
use Plasticode\Core\Core;
use Plasticode\Data\Db;
use Plasticode\Gallery\Gallery;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Cases;
use Psr\Container\ContainerInterface;
use Slim\Container;

final class GalleryLinkMapperTest extends BaseRenderTestCase
{
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
        
        $galleryPictureRepository = new GalleryPictureRepositoryMock(
            new GalleryPictureSeeder()
        );

        $this->mapper = new GalleryLinkMapper(
            $settingsProvider,
            $galleryPictureRepository,
            $this->renderer,
            $this->linker
        );
    }

    private function initModels() : void
    {
        $container = new Container(
            [
                'db' => function (ContainerInterface $c) {
                    return new Db($c);
                },

                'auth' => function (ContainerInterface $c) {
                    return $this->createStub(Auth::class);
                },

                'linker' => function (ContainerInterface $c) {
                    return $this->linker;
                },

                'cases' => function (ContainerInterface $c) {
                    return $this->createStub(Cases::class);
                },

                'parser' => function (ContainerInterface $c) {
                    return new CompositeParser();
                },

                'gallery' => function (ContainerInterface $c) {
                    return $this->createStub(Gallery::class);
                },

                'userRepository' => function (ContainerInterface $c) {
                    return $this->createStub(UserRepositoryInterface::class);
                },

                'roleRepository' => function (ContainerInterface $c) {
                    return $this->createStub(RoleRepositoryInterface::class);
                },

                'menuItemRepository' => function (ContainerInterface $c) {
                    return $this->createStub(MenuItemRepositoryInterface::class);
                },

                'tagRepository' => function (ContainerInterface $c) {
                    return $this->createStub(TagRepositoryInterface::class);
                }
            ]
        );

        Core::initModels($container);
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
                ''
            ],
            [
                ['gallery:1,2', '1', 'grid'],
                ''
            ],
            [
                ['gallery:Elves'],
                ''
            ],
            [
                ['gallery:Orcs'],
                null
            ],
        ];
    }
}
