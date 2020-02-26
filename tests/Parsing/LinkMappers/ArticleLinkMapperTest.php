<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\ArticleLinkMapper;
use App\Tests\BaseRenderTestCase;
use App\Tests\Mocks\LinkerMock;
use App\Tests\Mocks\Repositories\ArticleRepositoryMock;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\Mocks\Repositories\TagRepositoryMock;

final class ArticleLinkMapperTest extends BaseRenderTestCase
{
    /** @var ArticleLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new ArticleLinkMapper(
            new ArticleRepositoryMock(),
            new TagRepositoryMock(),
            $this->renderer,
            $linker,
            new TagLinkMapper($this->renderer, $linker)
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
                ['Illidan Stormrage'],
                '<span class="no-url">Illidan Stormrage</span>'
            ],
            [
                ['illidan-stormrage', 'Illidanchick'],
                '<span class="no-url" data-toggle="tooltip" title="illidan-stormrage">Illidanchick</span>'
            ],
            [
                ['about us'],
                '<a href="%page%/about-us" class="entity-url">about us</a>'
            ],
            [
                ['warcraft'],
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>'
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
                '<a href="%page%/about-us" class="entity-url">about us</a>',
                '<a href="' . $linker->page() . 'about-us" class="entity-url">about us</a>'
            ],
            [
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                '<a href="' . $linker->tag() . 'warcraft" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
