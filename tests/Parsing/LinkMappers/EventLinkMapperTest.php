<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\EventLinkMapper;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;
use Plasticode\Parsing\ParsingContext;

final class EventLinkMapperTest extends BaseRenderTestCase
{
    /** @var EventLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new EventLinkMapper($this->renderer, $linker);
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
                ['event:123'],
                '<a href="%event%/123" class="entity-url">123</a>'
            ],
            [
                ['event:5', 'Some great event!'],
                '<a href="%event%/5" class="entity-url">Some great event!</a>'
            ]
        ];
    }

    /**
     * @dataProvider renderLinksProvider
     *
     * @param string $original
     * @param string $expected
     * @return void
     */
    public function testRenderLinks(string $original, string $expected) : void
    {
        $context = ParsingContext::fromText($original);
        $renderedContext = $this->mapper->renderLinks($context);

        $this->assertEquals(
            $expected,
            $renderedContext->text
        );
    }

    public function renderLinksProvider() : array
    {
        $linker = new LinkerMock();

        return [
            [
                '<a href="%event%/123" class="entity-url">warcraft</a>',
                '<a href="' . $linker->event() . '123" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
