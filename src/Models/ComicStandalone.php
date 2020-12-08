<?php

namespace App\Models;

use App\Collections\ComicStandalonePageCollection;
use App\Models\Traits\ComicRoot;

/**
 * @property string $issuedOn
 * @property string $nameRu Override of ComicRoot's nullable.
 * @property string|null $origin
 * @method static withPages(ComicStandalonePageCollection|callable $pages)
 */
class ComicStandalone extends Comic
{
    use ComicRoot;

    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            ...$this->comicRootProperties(),
        ];
    }

    public function pages() : ComicStandalonePageCollection
    {
        return ComicStandalonePageCollection::from(
            parent::pages()
        );
    }

    /**
     * @return static|null
     */
    public function prev() : ?self
    {
        return null;
    }

    /**
     * @return static|null
     */
    public function next() : ?self
    {
        return null;
    }

    public function titleName() : string
    {
        return $this->fullName();
    }
}
