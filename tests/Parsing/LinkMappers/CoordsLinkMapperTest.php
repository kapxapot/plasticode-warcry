<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\CoordsLinkMapper;
use App\Tests\BaseRenderTestCase;
use App\Tests\Mocks\LinkerMock;
use App\Tests\Mocks\Repositories\LocationRepositoryMock;
use App\Tests\Seeders\LocationSeeder;

final class CoordsLinkMapperTest extends BaseRenderTestCase
{
    /** @var CoordsLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $locationRepository = new LocationRepositoryMock(new LocationSeeder());
        $linker = new LinkerMock();

        $this->mapper = new CoordsLinkMapper(
            $locationRepository,
            $this->renderer,
            $linker
        );
    }

    protected function tearDown() : void
    {
        unset($this->mapper);

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
                ['coords:123', '20', '40'],
                '<a href="wowhead.ru/maps?data=123:200400" class="no-wrap">[20, 40]</a>'
            ],
            [
                ['coords:Zangarmarsh', '10', '30'],
                '<a href="wowhead.ru/maps?data=1:100300" class="no-wrap">[10, 30]</a>'
            ],
            [
                ['coords:456'],
                null
            ],
        ];
    }
}
