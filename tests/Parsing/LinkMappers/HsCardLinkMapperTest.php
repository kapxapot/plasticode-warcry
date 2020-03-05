<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\HsCardLinkMapper;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;

final class HsCardLinkMapperTest extends BaseRenderTestCase
{
    /** @var HsCardLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new HsCardLinkMapper($this->renderer, $linker);
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
                ['card:lord-jaraxxus'],
                '<a href="http://hscards.com/lord-jaraxxus" class="hh-ttp">lord-jaraxxus</a>'
            ],
            [
                ['card:lord-jaraxxus', 'Lord Jaraxxus'],
                '<a href="http://hscards.com/lord-jaraxxus" class="hh-ttp">Lord Jaraxxus</a>'
            ]
        ];
    }
}
