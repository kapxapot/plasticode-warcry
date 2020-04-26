<?php

namespace App\Models;

use Plasticode\Moment;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;

/**
 * @property string|null $address
 * @property string|null $endsAt
 * @property string|null $description
 * @property integer $important
 * @property string $name
 * @property integer|null $regionId
 * @property string $startsAt
 * @property integer $typeId
 * @property integer $unknownEnd
 * @property string|null $website
 * @method Region|null region()
 * @method EventType type()
 * @method static withRegion(Region|callable|null $region)
 * @method static withType(EventType|callable $type)
 */
class Event extends NewsSource
{
    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'region',
            'type'
        ];
    }

    public function started() : bool
    {
        return Date::happened($this->startsAt);
    }

    public function ended() : bool
    {
        return ($this->unknownEnd != 1)
            && Date::happened($this->guessEndsAt());
    }

    public function start() : Moment
    {
        return new Moment($this->startsAt);
    }

    public function end() : ?Moment
    {
        return $this->endsAt
            ? new Moment($this->endsAt)
            : null;
    }

    /**
     * @return string|\DateTime
     */
    public function guessEndsAt()
    {
        return $this->endsAt ?? Date::endOfDay($this->startsAt);
    }

    public function toString() : string
    {
        return '[' . $this->getId() . '] ' . $this->name;
    }

    // SearchableInterface

    public function code() : string
    {
        return Strings::doubleBracketsTag('event', $this->getId(), $this->name);
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
