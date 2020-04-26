<?php

namespace App\Collections;

use App\Models\Event;
use Plasticode\TypedCollection;

class EventCollection extends TypedCollection
{
    protected string $class = Event::class;

    public function groups() : array
    {
        return [
            [
                'id' => 'current',
                'label' => 'Текущие',
                'items' => $this->where(
                    fn (Event $e) => $e->started() && !$e->ended()
                ),
            ],
            [
                'id' => 'future',
                'label' => 'Будущие',
                'items' => $this->where(
                    fn (Event $e) => !$e->started()
                ),
            ],
            [
                'id' => 'past',
                'label' => 'Прошедшие',
                'items' => $this->where(
                    fn (Event $e) => $e->ended()
                ),
            ]
        ];
    }
}
