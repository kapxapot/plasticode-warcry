<?php

namespace App\Tests\Mocks;

use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;

class SettingsProviderMock implements SettingsProviderInterface
{
    private $settings = [
        'gallery' => [
            'inline_limit' => 3,
        ],
    ];

    public function getSettings(string $path = null, $default = null)
    {
        return Arrays::get($this->settings, $path) ?? $default;
    }
}
