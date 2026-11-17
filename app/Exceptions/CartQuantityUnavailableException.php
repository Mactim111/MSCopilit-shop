<?php

namespace App\Exceptions;

use RuntimeException;

class CartQuantityUnavailableException extends RuntimeException
{
    public function __construct(public readonly int $available, ?string $message = null)
    {
        parent::__construct($message ?? "Данное количество недоступно к заказу. В наличии имеется {$available} единиц товара.");
    }
}
