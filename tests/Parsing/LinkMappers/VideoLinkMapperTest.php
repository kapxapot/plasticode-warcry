<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\VideoLinkMapper;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;
use Plasticode\Parsing\ParsingContext;

final class VideoLinkMapperTest extends BaseRenderTestCase
{
    /** @var VideoLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new VideoLinkMapper($this->renderer, $linker);
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
                ['video:123'],
                '<a href="%video%/123" class="entity-url">123</a>'
            ],
            [
                ['video:5', 'Some great video!'],
                '<a href="%video%/5" class="entity-url">Some great video!</a>'
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
                '<a href="%video%/123" class="entity-url">warcraft</a>',
                '<a href="' . $linker->video() . '123" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
