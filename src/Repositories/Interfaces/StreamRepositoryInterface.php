<?php

namespace App\Repositories\Interfaces;

use App\Models\Stream;

interface StreamRepositoryInterface
{
    function save(Stream $stream) : Stream;
}
