<?php

namespace App\Exceptions;

use RuntimeException;

class CartOrderAvailabilityException extends RuntimeException
{
    public function __construct(
        public readonly string $title,
        public readonly int $available,
        public readonly int $requested,
        public readonly bool $activeForSale = true
    ) {
        $message = !$activeForSale
            ? "Товар «{$title}» снят с продажи. Заказ не может быть оформлен."
            : ($available > 0
                ? "Данное количество товара «{$title}» недоступно к заказу. В наличии имеется {$available} единиц товара."
                : "Товар «{$title}» закончился. Заказ не может быть оформлен.");

        parent::__construct($message);
    }
}
