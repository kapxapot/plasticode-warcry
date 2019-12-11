<?php

namespace App\Core\Interfaces;

use Plasticode\Core\Interfaces\LinkerInterface as PlasticodeLinkerInterface;

interface LinkerInterface extends PlasticodeLinkerInterface
{
    /**
     * Get article link.
     *
     * @param int|string $id
     * @param string $cat
     * @return string
     */
    public function article($id = null, ?string $cat = null) : string;

    public function news(int $id = null) : string;
    public function event(int $id = null) : string;
    public function video(int $id = null) : string;
    public function stream(string $alias = null) : string;
    public function hsCard(string $id) : string;
}
