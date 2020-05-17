<?php

namespace App\Collections\Traits;

use App\Models\Interfaces\NumberedInterface;

trait NumberedCollection
{
    public function byNumber(int $number) : ?NumberedInterface
    {
        return $this->first(
            fn (NumberedInterface $n) => $n->number() == $number
        );
    }

    public function prevBy(int $number) : ?NumberedInterface
    {
        return $this
            ->desc(
                fn (NumberedInterface $n) => $n->number()
            )
            ->first(
                fn (NumberedInterface $n) => $n->number() < $number
            );
    }

    public function nextBy(int $number) : ?NumberedInterface
    {
        return $this
            ->asc(
                fn (NumberedInterface $n) => $n->number
            )
            ->first(
                fn (NumberedInterface $n) => $n->number > $number
            );
    }

    public function maxNumber() : int
    {
        $max = $this
            ->scalarize(
                fn (NumberedInterface $n) => $n->number
            )
            ->max();

        return $max ?? 0;
    }
}
