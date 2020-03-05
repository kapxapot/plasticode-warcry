<?php

namespace App\Tests;

use App\Core\Interfaces\RendererInterface;
use App\Testing\Factories\RendererFactory;
use PHPUnit\Framework\TestCase;

abstract class BaseRenderTestCase extends TestCase
{
    /** @var RendererInterface */
    protected $renderer;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->renderer = RendererFactory::make();
    }

    protected function tearDown() : void
    {
        unset($this->renderer);

        parent::tearDown();
    }
}
