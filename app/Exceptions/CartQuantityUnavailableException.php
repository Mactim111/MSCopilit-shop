<?php

namespace App\Exceptions;

use RuntimeException;

class CartQuantityUnavailableException extends RuntimeException
{
    public function __construct(public readonly int $available)
    {
        parent::__construct(
            "Данное количество недоступно к заказу. В наличии имеется {$available} единиц товара."
        );
    }
}
