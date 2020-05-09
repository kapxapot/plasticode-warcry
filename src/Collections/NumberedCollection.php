<?php

namespace App\Collections;

use App\Models\Interfaces\NumberedInterface as NI;
use Plasticode\Collections\Basic\DbModelCollection;

abstract class NumberedCollection extends DbModelCollection
{
    protected string $class = NI::class;

    public function byNumber(int $number) : ?NI
    {
        return $this->first(
            fn (NI $n) => $n->number() == $number
        );
    }

    public function prev(int $number) : ?NI
    {
        return $this
            ->desc(
                fn (NI $n) => $n->number()
            )
            ->first(
                fn (NI $n) => $n->number() < $number
            );
    }

    public function next(int $number) : ?NI
    {
        return $this
            ->asc(
                fn (NI $n) => $n->number
            )
            ->first(
                fn (NI $n) => $n->number > $number
            );
    }

    public function maxNumber() : int
    {
        $max = $this
            ->asc(
                fn (NI $n) => $n->number
            )
            ->last();

        return $max ? $max->number : 0;
    }
}
