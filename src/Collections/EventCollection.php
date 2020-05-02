<?php

namespace App\Collections;

use App\Models\Event;

class EventCollection extends NewsSourceCollection
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
