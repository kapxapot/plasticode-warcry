<?php

namespace App\Tests\Parsing\LinkMappers;

use App\Parsing\LinkMappers\SpellLinkMapper;
use App\Testing\Factories\RecipeRepositoryFactory;
use App\Testing\Mocks\LinkerMock;
use App\Tests\BaseRenderTestCase;

final class SpellLinkMapperTest extends BaseRenderTestCase
{
    private SpellLinkMapper $mapper;

    protected function setUp() : void
    {
        parent::setUp();

        $linker = new LinkerMock();

        $recipeRepository = RecipeRepositoryFactory::make($linker);

        $this->mapper = new SpellLinkMapper(
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
                ['spell:1'],
                '<a href="http://abs/recipes/1" data-toggle="tooltip" title="Рецепт: Золотой слиток" rel="spell=1&amp;domain=ru">[~]</a>'
            ],
            [
                ['spell:1', 'Some cool spell'],
                '<a href="http://abs/recipes/1" data-toggle="tooltip" title="Рецепт: Some cool spell" rel="spell=1&amp;domain=ru">Some cool spell</a>'
            ],
            [
                ['spell:2'],
                '<a href="wowhead.ru/spell=2" data-wowhead="spell=2">2</a>'
            ]
        ];
    }
}
