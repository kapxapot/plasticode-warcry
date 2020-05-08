<?php

namespace App\Models;

use App\Collections\ComicStandalonePageCollection;
use App\Models\Traits\ComicRoot;

/**
 * @property string $issuedOn
 * @property string $nameRu
 * @property string|null $origin
 * @method ComicStandalonePageCollection pages()
 * @method static withPages(ComicStandalonePageCollection|callable $pages)
 */
class ComicStandalone extends Comic
{
    use ComicRoot;

    public function createPage() : ComicStandalonePage
    {
        return ComicStandalonePage::createForComic($this);
    }

    public function titleName() : string
    {
        return $this->fullName();
    }
}
