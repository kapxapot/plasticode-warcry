<?php

namespace App\Tests\Models;

use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Seeders\GameSeeder;
use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase
{
    protected GameRepositoryInterface $gameRepository;

    protected function setUp() : void
    {
        parent::setUp();

        $this->gameRepository = new GameRepositoryMock(
            new GameSeeder()
        );
    }

    protected function tearDown() : void
    {
        unset($this->gameRepository);

        parent::tearDown();
    }

    public function testSubTree() : void
    {
        $game = $this->gameRepository->get(1);

        $this->assertEqualsCanonicalizing(
            [1, 2, 3, 4],
            $game->subTree()->ids()->toArray()
        );
    }
}
