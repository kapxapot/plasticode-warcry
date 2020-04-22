<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\ItemLinkMapper;
use App\Testing\Factories\RecipeRepositoryFactory;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;

final class ItemLinkMapperTest extends BaseRenderTestCase
{
    private ItemLinkMapper $mapper;

    protected function setUp() : void
    {
        parent::setUp();

        $linker = new LinkerMock();

        $recipeRepository = RecipeRepositoryFactory::make($linker);

        $this->mapper = new ItemLinkMapper(
            $recipeRepository,
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
                ['item:1'],
                '<a href="wowhead.ru/item=1" data-wowhead="item=1">1</a> <a href="http://abs/recipes/1" data-toggle="tooltip" title="Рецепт: Золотой слиток" rel="spell=1&amp;domain=ru">[~]</a>'
            ],
            [
                ['item:1', 'Some cool item'],
                '<a href="wowhead.ru/item=1" data-wowhead="item=1">Some cool item</a> <a href="http://abs/recipes/1" data-toggle="tooltip" title="Рецепт: Золотой слиток" rel="spell=1&amp;domain=ru">[~]</a>'
            ],
            [
                ['item:2'],
                '<a href="wowhead.ru/item=2" data-wowhead="item=2">2</a>'
            ]
        ];
    }
}
