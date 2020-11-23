<?php

namespace App\Testing\Mocks;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;

class SettingsProviderMock implements SettingsProviderInterface
{
    private $settings = [
        'gallery' => [
            'inline_limit' => 3,
        ],
    ];

    public function get(string $path, $default = null)
    {
        return Arrays::get($this->settings, $path) ?? $default;
    }
}
