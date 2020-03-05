<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\GenericLinkMapper;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;

final class GenericLinkMapperTest extends BaseRenderTestCase
{
    /** @var GenericLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();

        $linker = new LinkerMock();

        $this->mapper = new GenericLinkMapper(
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
                ['faction:1'],
                '<a href="wowhead.ru/faction=1" data-wowhead="faction=1">1</a>'
            ],
            [
                ['faction:1', 'Some faction'],
                '<a href="wowhead.ru/faction=1" data-wowhead="faction=1">Some faction</a>'
            ],
            [
                ['ach:1'],
                '<a href="wowhead.ru/achievement=1" data-wowhead="achievement=1">1</a>'
            ],
            [
                ['wowevent:1'],
                '<a href="wowhead.ru/event=1" data-wowhead="event=1">1</a>'
            ]
        ];
    }
}
