<?php

namespace App\Models;

use App\Models\Interfaces\NumberedInterface;
use App\Models\Traits\PageUrl;
use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Published;

/**
 * @property integer $number
 * @property string $picType
 * @method string extension()
 * @method string thumbUrl()
 * @method string url()
 * @method static withExtension(string|callable $ext)
 * @method static withThumbUrl(string|callable $thumbUrl)
 * @method static withUrl(string|callable $url)
 */
abstract class ComicPage extends DbModel implements NumberedInterface
{
    use PageUrl;
    use Published;
    use Stamps;

    protected static string $comicIdField;

    protected string $comicPropertyName = 'comic';

    public static function comicIdField() : string
    {
        return static::$comicIdField;
    }

    protected function requiredWiths() : array
    {
        return [
            $this->comicPropertyName,
            $this->pageUrlPropertyName,
            'extension',
            'thumbUrl',
            'url',
        ];
    }

    abstract public function comicId() : int;

    /**
     * @return static|null
     */
    public function prev() : ?self
    {
        return $this->comic()->prevPage($this->number);
    }

    /**
     * @return static|null
     */
    public function next() : ?self
    {
        return $this->comic()->nextPage($this->number);
    }

    public function comic() : Comic
    {
        return $this->getWithProperty(
            $this->comicPropertyName
        );
    }

    public function number() : int
    {
        return $this->number;
    }

    public function numberStr() : string
    {
        return str_pad($this->number, 2, '0', STR_PAD_LEFT);
    }

    public function fileName() : string
    {
        return $this->getId() . '.' . $this->extension();
    }

    public function titleName() : string
    {
        return $this->numberStr() . ' - ' . $this->comic()->titleName();
    }
}
