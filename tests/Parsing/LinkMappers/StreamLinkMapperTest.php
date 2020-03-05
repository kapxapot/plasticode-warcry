<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\StreamLinkMapper;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;
use Plasticode\Parsing\ParsingContext;

final class StreamLinkMapperTest extends BaseRenderTestCase
{
    /** @var StreamLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new StreamLinkMapper($this->renderer, $linker);
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
                ['stream:123'],
                '<a href="%stream%/123" class="entity-url">123</a>'
            ],
            [
                ['stream:5', 'Some great stream!'],
                '<a href="%stream%/5" class="entity-url">Some great stream!</a>'
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
                '<a href="%stream%/123" class="entity-url">warcraft</a>',
                '<a href="' . $linker->stream() . '123" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
