<?php

namespace App\Core\Interfaces;

use App\Models\GalleryPicture;
use App\Models\Game;
use Plasticode\Core\Interfaces\LinkerInterface as PlasticodeLinkerInterface;

interface LinkerInterface extends PlasticodeLinkerInterface
{
    public function game(?Game $game) : string;

    /**
     * Get article link.
     *
     * @param int|string $id
     * @param string $cat
     * @return string
     */
    public function article($id = null, ?string $cat = null) : string;

    public function event(int $id = null) : string;
    public function video(int $id = null) : string;
    public function stream(string $alias = null) : string;

    public function galleryPictureImg(GalleryPicture $picture) : string;
    public function galleryThumbImg(GalleryPicture $picture) : string;

    public function disqusNews(int $id) : string;

    public function wowheadIcon(string $icon) : string;
    public function wowheadUrl(string $params) : string;
    public function wowheadUrlRu(string $params) : string;
    public function wowheadSpellRu(int $id) : string;
    public function wowheadItemRu(int $id) : string;
    public function wowheadItem(int $id) : string;
    public function wowheadItemXml(int $id) : string;
    public function wowheadItemRuXml(int $id) : string;

    public function hsCard(string $id) : string;
}
