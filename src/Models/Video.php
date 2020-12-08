<?php

namespace App\Models;

use Plasticode\Util\Strings;

/**
 * @property integer $announce
 * @property string|null $description
 * @property string $name
 * @property string $youtubeCode
 * @method static withVideo(string|callable|null $video)
 */
class Video extends NewsSource
{
    protected string $videoPropertyName = 'video';

    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            $this->videoPropertyName
        ];
    }

    public function video() : ?string
    {
        return $this->getWithProperty(
            $this->videoPropertyName
        );
    }

    public function toString() : string
    {
        return '[' . $this->getId() . '] ' . $this->name;
    }

    // SearchableInterface

    public function code() : string
    {
        return Strings::doubleBracketsTag('video', $this->getId(), $this->name);
    }

    // SerializableInterface

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'tags' => Strings::toTags($this->tags),
        ];
    }

    // NewsSourceInterface

    public function displayTitle() : string
    {
        return $this->name;
    }

    public function rawText() : ?string
    {
        return $this->description;
    }
}
